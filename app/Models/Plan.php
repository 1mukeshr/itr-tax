<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'name', 'slug', 'description', 'price', 'features', 'itr_types',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function filings(): HasMany
    {
        return $this->hasMany(ItrFiling::class);
    }

    public function featuresList(): array
    {
        $decoded = json_decode($this->features ?? '[]', true);

        return is_array($decoded) ? $decoded : [];
    }
}
