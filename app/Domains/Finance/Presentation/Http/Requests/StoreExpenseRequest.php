<?php

namespace App\Domains\Finance\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('record-expense', $this->route('occasion'));
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
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
            'spent_at' => ['required', 'date', Rule::date()->beforeOrEqual('today')],
        ];
    }
}
