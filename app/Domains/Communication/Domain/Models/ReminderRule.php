<?php

namespace App\Domains\Communication\Domain\Models;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Models\TimelineEvent;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\ReminderRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderRule extends Model
{
    use HasFactory, HasUuid;

    protected static function newFactory(): ReminderRuleFactory
    {
        return ReminderRuleFactory::new();
    }

    protected $fillable = [
        'occasion_id',
        'timeline_event_id',
        'offset_minutes',
        'created_by',
        'triggered_at',
    ];

    protected function casts(): array
    {
        return [
            'triggered_at' => 'datetime',
        ];
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function timelineEvent(): BelongsTo
    {
        return $this->belongsTo(TimelineEvent::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
