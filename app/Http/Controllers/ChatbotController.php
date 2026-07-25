<?php

namespace App\Http\Controllers;

use App\Models\ChatbotMessage;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function suggestions(): JsonResponse
    {
        $faqs = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get(['id', 'question', 'category']);

        return response()->json([
            'ok' => true,
            'suggestions' => $faqs->map(fn (Faq $f) => [
                'id' => $f->id,
                'question' => $f->question,
                'category' => $f->category,
            ]),
        ]);
    }

    public function ask(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string|max:64',
        ]);

        $sessionId = ($data['session_id'] ?? '') ?: (string) Str::uuid();
        $message = trim($data['message']);
        $userId = Auth::id();

        ChatbotMessage::create([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'role' => 'user',
            'message' => $message,
        ]);

        [$answer, $faq, $score] = $this->matchFaq($message);

        ChatbotMessage::create([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'role' => 'bot',
            'message' => $answer,
            'matched_faq_id' => $faq?->id,
            'match_score' => $score,
        ]);

        return response()->json([
            'ok' => true,
            'session_id' => $sessionId,
            'reply' => $answer,
            'matched' => (bool) $faq,
            'faq' => $faq ? [
                'id' => $faq->id,
                'question' => $faq->question,
                'category' => $faq->category,
            ] : null,
            'score' => $score,
        ]);
    }

    /** @return array{0: string, 1: ?Faq, 2: float} */
    private function matchFaq(string $message): array
    {
        $faqs = Faq::query()->where('is_active', true)->orderBy('sort_order')->get();
        if ($faqs->isEmpty()) {
            return [$this->fallback(), null, 0.0];
        }

        $tokens = $this->tokens($message);
        if ($tokens === []) {
            return [$this->fallback(), null, 0.0];
        }

        $best = null;
        $bestScore = 0.0;

        foreach ($faqs as $faq) {
            $hay = strtolower($faq->question.' '.$faq->answer.' '.($faq->category ?? ''));
            $hits = 0;
            foreach ($tokens as $token) {
                if (str_contains($hay, $token)) {
                    $hits++;
                }
            }
            $score = $hits / max(count($tokens), 1);

            // Bonus for phrase overlap with question title
            similar_text(strtolower($message), strtolower($faq->question), $pct);
            $score = max($score, $pct / 100);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $faq;
            }
        }

        if (! $best || $bestScore < 0.22) {
            return [$this->fallback($faqs->take(3)), null, round($bestScore, 2)];
        }

        return [$best->answer, $best, round($bestScore, 2)];
    }

    /** @return list<string> */
    private function tokens(string $text): array
    {
        $text = strtolower(preg_replace('/[^a-z0-9\s]/', ' ', $text) ?? '');
        $parts = preg_split('/\s+/', trim($text)) ?: [];
        $stop = ['a', 'an', 'the', 'is', 'are', 'do', 'i', 'my', 'to', 'for', 'of', 'and', 'or', 'on', 'in', 'how', 'what', 'when', 'can', 'me', 'please'];

        return array_values(array_filter($parts, fn ($t) => strlen($t) > 2 && ! in_array($t, $stop, true)));
    }

    private function fallback($topFaqs = null): string
    {
        $lines = [
            "I couldn't find an exact match in our knowledge base.",
            'Try asking about Form 16, e-verify, Self vs Expert filing, documents, or refunds.',
            'You can also browse FAQs or contact support.',
        ];

        if ($topFaqs && count($topFaqs)) {
            $lines[] = 'Popular topics:';
            foreach ($topFaqs as $faq) {
                $lines[] = '• '.$faq->question;
            }
        }

        return implode("\n", $lines);
    }
}
