<?php

namespace App\Support;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\ItrFiling;
use App\Models\User;

class ChatService
{
    /**
     * Ensure a chat thread exists for an assigned filing.
     */
    public static function openForFiling(ItrFiling $filing, ?string $welcome = null): ?ChatThread
    {
        if (! $filing->ca_id || ! $filing->user_id) {
            return null;
        }

        $thread = ChatThread::query()->firstOrCreate(
            ['filing_id' => $filing->id],
            [
                'user_id' => $filing->user_id,
                'ca_id' => $filing->ca_id,
                'status' => 'open',
                'last_message_at' => now(),
            ]
        );

        // If expert changed, keep thread linked to current assignment.
        if ((int) $thread->ca_id !== (int) $filing->ca_id) {
            $thread->update([
                'ca_id' => $filing->ca_id,
                'status' => 'open',
            ]);
        }

        if ($welcome && ! $thread->messages()->exists()) {
            self::postSystemStyle($thread, (int) $filing->ca_id, $welcome);
        }

        return $thread->fresh(['customer', 'expert', 'filing']);
    }

    public static function post(ChatThread $thread, User $sender, string $body): ChatMessage
    {
        $body = trim($body);
        $message = ChatMessage::create([
            'thread_id' => $thread->id,
            'sender_id' => $sender->id,
            'body' => $body,
        ]);

        $thread->update([
            'last_message_at' => $message->created_at,
            'status' => 'open',
        ]);

        return $message->load('sender');
    }

    private static function postSystemStyle(ChatThread $thread, int $senderId, string $body): void
    {
        $msg = ChatMessage::create([
            'thread_id' => $thread->id,
            'sender_id' => $senderId,
            'body' => $body,
        ]);
        $thread->update(['last_message_at' => $msg->created_at]);
    }
}
