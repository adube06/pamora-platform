<?php

namespace App\Domains\Finance\Domain\Models;

use App\Domains\Shared\Domain\Concerns\HasUuid;
use Database\Factories\BudgetCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetCategory extends Model
{
    use HasFactory, HasUuid;

    protected static function newFactory(): BudgetCategoryFactory
    {
        return BudgetCategoryFactory::new();
    }

    protected $fillable = [
        'budget_id',
        'name',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function budgetItems(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
