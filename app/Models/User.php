<?php

namespace App\Models;

use App\Domains\Communication\Domain\Models\Notification;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MustVerifyEmail, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'notification_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'notification_preferences' => 'array',
        ];
    }

    /**
     * Communication PRD FR-005 — opt-out model: a type missing from the
     * stored preferences (including for every user before this feature
     * existed) is treated as enabled.
     */
    public function wantsNotification(string $type): bool
    {
        return $this->notification_preferences[$type] ?? true;
    }

    /**
     * Overrides Notifiable's own notifications() (Laravel's built-in,
     * unused DatabaseNotification system) with the Communication
     * Domain's first-class Notification entity.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Platform-scoped gate for the Admin Portal (Filament) — independent
     * of any Occasion membership, per the Permission Catalog's own
     * "Platform-Scoped" section. A single boolean for now since V1.0
     * names only one admin tier.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }
}
