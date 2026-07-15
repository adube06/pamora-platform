<?php

namespace App\Domains\Finance\Presentation\Http\Resources;

use App\Domains\Finance\Domain\Models\BudgetCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BudgetCategory
 */
class BudgetCategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
        ];
    }
}
