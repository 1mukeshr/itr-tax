<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\ItrFiling;
use App\Support\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = ChatThread::query()
            ->with(['customer:id,name,email', 'expert:id,name,email', 'filing:id,status,itr_type,assessment_year', 'latestMessage.sender:id,name'])
            ->orderByDesc('last_message_at');

        if ($user->isCa()) {
            $query->where('ca_id', $user->id);
        } elseif ($user->isUser()) {
            $query->where('user_id', $user->id);
        } else {
            abort(403);
        }

        $threads = $query->paginate(20)->withQueryString();

        return view('chat.index', [
            'threads' => $threads,
            'role' => $user->isCa() ? 'ca' : 'user',
        ]);
    }

    public function openFiling(ItrFiling $filing)
    {
        $user = Auth::user();
        $this->assertFilingAccess($filing, $user);

        if (! $filing->ca_id) {
            return redirect()
                ->back()
                ->with('error', 'Chat opens after a tax expert is assigned to this filing.');
        }

        $thread = ChatService::openForFiling(
            $filing,
            'Hello! I am your assigned tax expert for filing #'.$filing->id.'. Ask me anything about your documents or tax summary.'
        );

        return redirect()->route('chat.show', $thread);
    }

    public function show(ChatThread $thread): View
    {
        $user = Auth::user();
        $this->assertThreadAccess($thread, $user);

        $thread->load(['customer:id,name,email', 'expert:id,name,email', 'filing:id,status,itr_type,assessment_year,pan']);

        // Mark peer messages as read
        ChatMessage::query()
            ->where('thread_id', $thread->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $thread->messages()
            ->with('sender:id,name')
            ->orderBy('id')
            ->limit(200)
            ->get();

        return view('chat.show', [
            'thread' => $thread,
            'messages' => $messages,
            'role' => $user->isCa() ? 'ca' : 'user',
            'counterpart' => $thread->counterpartFor($user),
        ]);
    }

    public function send(Request $request, ChatThread $thread)
    {
        $user = Auth::user();
        $this->assertThreadAccess($thread, $user);

        if ($thread->status === 'closed') {
            return back()->with('error', 'This chat is closed.');
        }

        $data = $request->validate([
            'body' => 'required|string|min:1|max:4000',
        ]);

        ChatService::post($thread, $user, $data['body']);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('chat.show', $thread);
    }

    public function poll(Request $request, ChatThread $thread): JsonResponse
    {
        $user = Auth::user();
        $this->assertThreadAccess($thread, $user);

        $afterId = (int) $request->query('after', 0);

        $messages = $thread->messages()
            ->with('sender:id,name')
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->limit(100)
            ->get();

        ChatMessage::query()
            ->where('thread_id', $thread->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $messages->map(fn (ChatMessage $m) => [
                'id' => $m->id,
                'body' => $m->body,
                'mine' => (int) $m->sender_id === (int) $user->id,
                'sender' => $m->sender?->name ?? 'User',
                'time' => optional($m->created_at)->format('d M · h:i A'),
            ]),
            'last_id' => $messages->last()?->id ?? $afterId,
        ]);
    }

    private function assertThreadAccess(ChatThread $thread, $user): void
    {
        if (! $user || ! $thread->isParticipant($user)) {
            abort(403, 'You cannot access this chat.');
        }
    }

    private function assertFilingAccess(ItrFiling $filing, $user): void
    {
        if ($user->isUser() && (int) $filing->user_id === (int) $user->id) {
            return;
        }
        if ($user->isCa() && (int) $filing->ca_id === (int) $user->id) {
            return;
        }
        abort(403);
    }
}
