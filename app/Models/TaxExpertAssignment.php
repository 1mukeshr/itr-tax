<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxExpertAssignment extends Model
{
    protected $table = 'tax_expert_assignments';

    protected $fillable = [
        'order_id', 'tax_expert_id', 'assigned_by', 'status', 'remark', 'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ItrFiling::class, 'order_id');
    }

    public function taxExpert(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tax_expert_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
