<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotMessage extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'role',
        'message',
        'matched_faq_id',
        'match_score',
    ];

    protected function casts(): array
    {
        return [
            'match_score' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function faq(): BelongsTo
    {
        return $this->belongsTo(Faq::class, 'matched_faq_id');
    }
}
