<?php

namespace App\Domains\Planning\Domain\Models;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\MilestoneFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Milestone extends Model
{
    use HasFactory, HasUuid;

    protected static function newFactory(): MilestoneFactory
    {
        return MilestoneFactory::new();
    }

    protected $fillable = [
        'occasion_id',
        'name',
        'created_by',
    ];

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * BR-017: Milestones are achieved automatically when all required
     * conditions are satisfied — computed fresh from linked Tasks' live
     * status, never stored, so reopening a linked Task un-achieves the
     * Milestone for free with no separate un-achievement logic.
     */
    public function isAchieved(): bool
    {
        return $this->tasks->isNotEmpty() && $this->tasks->every(fn (Task $task) => $task->status === TaskStatus::Completed);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
