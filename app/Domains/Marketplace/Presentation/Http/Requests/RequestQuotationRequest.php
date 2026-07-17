<?php

namespace App\Domains\Marketplace\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('request-quotation', $this->route('occasion'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')],
            'message' => ['nullable', 'string'],
        ];
    }
}
