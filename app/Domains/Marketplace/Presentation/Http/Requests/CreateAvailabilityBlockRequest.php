<?php

namespace App\Domains\Marketplace\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAvailabilityBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('vendor')->owner_id === $this->user()->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
