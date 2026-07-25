<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusLog extends Model
{
    protected $table = 'activity_logs';

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'filing_id', 'user_id', 'action', 'old_status', 'new_status', 'changed_by', 'remark', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function filing(): BelongsTo
    {
        return $this->belongsTo(ItrFiling::class, 'filing_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
