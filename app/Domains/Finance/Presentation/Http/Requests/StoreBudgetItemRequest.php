<?php

namespace App\Domains\Finance\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit-budget', $this->route('occasion'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $budget = $this->route('occasion')->budget;

        return [
            'budget_category_id' => [
                'required',
                'integer',
                Rule::exists('budget_categories', 'id')->where('budget_id', $budget?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'estimated_cost' => ['required', 'numeric', 'min:1'],
        ];
    }
}
