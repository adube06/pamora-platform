<?php

namespace App\Domains\Finance\Presentation\Http\Resources;

use App\Domains\Finance\Domain\Models\BudgetItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BudgetItem
 */
class BudgetItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'estimated_cost' => $this->estimated_cost,
            'currency' => $this->currency,
            'category' => new BudgetCategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
