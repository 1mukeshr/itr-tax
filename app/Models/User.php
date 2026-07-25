<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role_id', 'status', 'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $with = ['roleRelation', 'profile'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
        ];
    }

    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /** Role slug for middleware / checks (user|ca|admin). */
    public function getRoleAttribute(): string
    {
        return $this->roleRelation?->slug ?? 'user';
    }

    public function getPanAttribute(): ?string
    {
        return $this->profile?->pan;
    }

    public function getAvatarAttribute(): ?string
    {
        return $this->profile?->avatar;
    }

    public function getAddressAttribute(): ?string
    {
        return $this->profile?->address;
    }

    public function getCityAttribute(): ?string
    {
        return $this->profile?->city;
    }

    public function getStateAttribute(): ?string
    {
        return $this->profile?->state;
    }

    public function getPincodeAttribute(): ?string
    {
        return $this->profile?->pincode;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCa(): bool
    {
        return $this->role === 'ca';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function scopeWithRole($query, string $slug)
    {
        return $query->whereHas('roleRelation', fn ($q) => $q->where('slug', $slug));
    }

    public function filings(): HasMany
    {
        return $this->hasMany(ItrFiling::class);
    }

    public function assignedFilings(): HasMany
    {
        return $this->hasMany(ItrFiling::class, 'ca_id');
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'author_id');
    }

    /** @deprecated use profile() */
    public function caProfile(): HasOne
    {
        return $this->profile();
    }
}
