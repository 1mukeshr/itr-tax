<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRequest extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'filing_id', 'ca_id', 'message', 'required_docs', 'status',
    ];

    public function filing(): BelongsTo
    {
        return $this->belongsTo(ItrFiling::class, 'filing_id');
    }

    public function ca(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ca_id');
    }
}
