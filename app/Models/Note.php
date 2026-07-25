<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    protected $table = 'tax_expert_notes';

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'filing_id', 'author_id', 'note', 'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function filing(): BelongsTo
    {
        return $this->belongsTo(ItrFiling::class, 'filing_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
