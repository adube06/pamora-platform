<?php

namespace App\Domains\Occasion\Domain\Models;

use App\Domains\Finance\Domain\Models\Budget;
use App\Domains\Finance\Domain\Models\Contribution;
use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Enums\OccasionType;
use App\Domains\Occasion\Domain\Enums\OccasionVisibility;
use App\Domains\People\Domain\Models\Invitation;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\OccasionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Occasion extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected static function newFactory(): OccasionFactory
    {
        return OccasionFactory::new();
    }

    protected $fillable = [
        'host_id',
        'slug',
        'title',
        'type',
        'description',
        'primary_date',
        'timezone',
        'location',
        'visibility',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => OccasionType::class,
            'visibility' => OccasionVisibility::class,
            'status' => OccasionStatus::class,
            'primary_date' => 'date',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(OccasionMember::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function budget(): HasOne
    {
        return $this->hasOne(Budget::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function memberFor(User $user): ?OccasionMember
    {
        return $this->members->firstWhere('user_id', $user->id);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
