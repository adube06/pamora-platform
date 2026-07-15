<?php

namespace App\Domains\Communication\Domain\Models;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory, HasUuid;

    protected static function newFactory(): NotificationFactory
    {
        return NotificationFactory::new();
    }

    protected $fillable = [
        'user_id',
        'occasion_id',
        'subject_type',
        'subject_id',
        'type',
        'title',
        'body',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
