<?php

namespace App\Domains\Finance\Presentation\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'planned_amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'max:8'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->route('occasion')->budget !== null) {
                $validator->errors()->add('name', 'This Occasion already has a Budget.');
            }
        });
    }
}
