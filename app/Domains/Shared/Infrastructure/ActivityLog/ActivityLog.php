<?php

namespace App\Domains\Shared\Infrastructure\ActivityLog;

use App\Domains\Shared\Domain\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

/**
 * Append-only. Business Rule BR-036: every significant action must
 * create an Activity Log entry. Entries are written exclusively by
 * AuditLogSubscriber reacting to domain events — never written to
 * directly by a domain, and never updated after creation.
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

    protected static function booted(): void
    {
        static::creating(function (self $log) {
            $log->created_at ??= now();
        });
    }
}
