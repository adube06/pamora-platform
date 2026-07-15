<?php

namespace App\Domains\Planning\Domain\Models;

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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
