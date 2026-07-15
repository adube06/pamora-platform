<?php

namespace App\Domains\Finance\Presentation\Http\Resources;

use App\Domains\Finance\Domain\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Expense
 */
class ExpenseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'spent_at' => $this->spent_at->toDateString(),
            'category' => new BudgetCategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
