<?php

namespace App\Domains\Planning\Domain\Models;

use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Enums\TaskPriority;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }

    protected $fillable = [
        'occasion_id',
        'checklist_id',
        'title',
        'description',
        'status',
        'priority',
        'assignee_id',
        'due_date',
        'completed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(OccasionMember::class, 'assignee_id');
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function milestones(): BelongsToMany
    {
        return $this->belongsToMany(Milestone::class);
    }

    /**
     * Tasks this Task depends on.
     */
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'task_dependencies', 'task_id', 'depends_on_task_id');
    }

    /**
     * Tasks that depend on this one — the inverse of dependencies(), used
     * for cycle detection when adding a new dependency.
     */
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'task_dependencies', 'depends_on_task_id', 'task_id');
    }

    /**
     * Computed fresh from dependencies' live status, never stored — same
     * reasoning as Milestone::isAchieved(). Assumes dependencies() is
     * already loaded by the caller.
     */
    public function isBlocked(): bool
    {
        return $this->dependencies->contains(fn (self $dependency) => $dependency->status !== TaskStatus::Completed);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(MediaAsset::class, 'attachable');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
