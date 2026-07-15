<?php

namespace App\Domains\Planning\Presentation\Http\Resources;

use App\Domains\Planning\Domain\Models\Checklist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Checklist
 */
class ChecklistResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
