<?php

namespace App\Domains\People\Domain\Models;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\OccasionMemberStatus;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;
use Database\Factories\OccasionMemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OccasionMember extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected static function newFactory(): OccasionMemberFactory
    {
        return OccasionMemberFactory::new();
    }

    protected $fillable = [
        'occasion_id',
        'user_id',
        'invitation_id',
        'status',
        'responsibilities',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'status' => OccasionMemberStatus::class,
            'responsibilities' => 'array',
            'permissions' => 'array',
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

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    /**
     * Occasion-scoped authorization check (BR-039). This is the single
     * enforcement point every Policy in every domain calls into — never
     * check permissions inline in a controller.
     */
    public function hasPermission(Permission $permission): bool
    {
        return in_array($permission->value, $this->permissions ?? [], strict: true);
    }
}
