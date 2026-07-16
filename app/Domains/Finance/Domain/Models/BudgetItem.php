<?php

namespace App\Domains\Finance\Domain\Models;

use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\BudgetItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    use HasFactory, HasUuid;

    protected static function newFactory(): BudgetItemFactory
    {
        return BudgetItemFactory::new();
    }

    protected $fillable = [
        'budget_category_id',
        'name',
        'estimated_cost',
        'currency',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
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
