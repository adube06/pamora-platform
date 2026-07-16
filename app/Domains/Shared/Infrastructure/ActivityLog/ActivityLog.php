<?php

namespace App\Domains\Shared\Infrastructure\ActivityLog;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only. Business Rule BR-036: every significant action must
 * create an Activity Log entry. Entries are written almost exclusively
 * by AuditLogSubscriber reacting to domain events — never written to
 * directly by a domain, and never updated after creation. The one
 * documented exception is the Admin Portal (Filament), whose CRUD save
 * lifecycle doesn't go through the Service+Event pipeline (ADR-006) —
 * see EditUser/EditOccasion's afterSave() hooks.
 */
class ActivityLog extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'occasion_id',
        'user_id',
        'subject_type',
        'subject_id',
        'action',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $log) {
            $log->created_at ??= now();
        });
    }
}
