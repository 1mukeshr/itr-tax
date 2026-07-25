<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ItrFiling extends Model
{
    protected $table = 'itr_orders';

    protected $fillable = [
        'user_id', 'ca_id', 'plan_id', 'assessment_year', 'itr_type', 'filing_mode',
        'income_profile', 'tax_regime', 'gross_salary', 'total_deductions',
        'ais_tds', 'form16_tds',
        'tax_old_regime', 'tax_new_regime', 'status', 'pan', 'notes',
        'acknowledgement_no', 'everify_status', 'everified_at', 'filed_at',
        'coupon_id', 'discount_amount', 'amount',
    ];

    protected function casts(): array
    {
        return [
            'gross_salary' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'ais_tds' => 'decimal:2',
            'form16_tds' => 'decimal:2',
            'tax_old_regime' => 'decimal:2',
            'tax_new_regime' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'amount' => 'decimal:2',
            'filed_at' => 'datetime',
            'everified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ca(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ca_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'filing_id');
    }

    public function documentRequests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class, 'filing_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'filing_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'filing_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class, 'filing_id');
    }

    public function chatThread(): HasOne
    {
        return $this->hasOne(ChatThread::class, 'filing_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(StatusLog::class, 'filing_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TaxExpertAssignment::class, 'order_id');
    }
}
