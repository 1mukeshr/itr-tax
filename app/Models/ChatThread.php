<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatThread extends Model
{
    protected $fillable = [
        'filing_id',
        'user_id',
        'ca_id',
        'status',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function filing(): BelongsTo
    {
        return $this->belongsTo(ItrFiling::class, 'filing_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function expert(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ca_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'thread_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class, 'thread_id')->latestOfMany();
    }

    public function unreadCountFor(int $viewerId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $viewerId)
            ->whereNull('read_at')
            ->count();
    }

    public function counterpartFor(User $viewer): ?User
    {
        if ((int) $viewer->id === (int) $this->user_id) {
            return $this->expert;
        }
        if ((int) $viewer->id === (int) $this->ca_id) {
            return $this->customer;
        }

        return null;
    }

    public function isParticipant(User $viewer): bool
    {
        return (int) $viewer->id === (int) $this->user_id
            || (int) $viewer->id === (int) $this->ca_id;
    }
}
