<?php

namespace App\Domains\People\Domain\Models;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\InvitationStatus;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\InvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use HasFactory, HasUuid;

    protected static function newFactory(): InvitationFactory
    {
        return InvitationFactory::new();
    }

    protected $fillable = [
        'occasion_id',
        'invited_by',
        'email',
        'status',
        'responsibilities',
        'permissions',
        'token',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvitationStatus::class,
            'responsibilities' => 'array',
            'permissions' => 'array',
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public static function generateToken(): string
    {
        return Str::random(48);
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === InvitationStatus::Pending && ! $this->isExpired();
    }
}
