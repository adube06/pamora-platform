<?php

namespace App\Domains\Finance\Presentation\Http\Resources;

use App\Domains\Finance\Domain\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Budget
 */
class BudgetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'currency' => $this->currency,
            'planned_amount' => $this->planned_amount,
            'status' => $this->status->value,
            'categories' => BudgetCategoryResource::collection($this->whenLoaded('categories')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
