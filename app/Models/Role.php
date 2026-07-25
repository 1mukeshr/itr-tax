<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function idFor(string $slug): int
    {
        $id = (int) static::query()->where('slug', $slug)->value('id');
        if ($id < 1) {
            throw new \RuntimeException("Role '{$slug}' is not seeded. Run: php artisan db:seed");
        }

        return $id;
    }
}
