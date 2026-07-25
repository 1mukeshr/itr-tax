<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'filing_id', 'uploaded_by', 'acknowledgement_no', 'file_path', 'original_name',
    ];

    public function filing(): BelongsTo
    {
        return $this->belongsTo(ItrFiling::class, 'filing_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
