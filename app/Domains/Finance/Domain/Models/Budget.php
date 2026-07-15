<?php

namespace App\Domains\Finance\Domain\Models;

use App\Domains\Finance\Domain\Enums\BudgetStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use HasFactory, HasUuid;

    protected static function newFactory(): BudgetFactory
    {
        return BudgetFactory::new();
    }

    protected $fillable = [
        'occasion_id',
        'name',
        'currency',
        'planned_amount',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => BudgetStatus::class,
            'planned_amount' => 'decimal:2',
        ];
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(BudgetCategory::class);
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
