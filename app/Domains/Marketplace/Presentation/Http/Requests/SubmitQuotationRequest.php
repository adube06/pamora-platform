<?php

namespace App\Domains\Marketplace\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('quotation')->service->vendor->owner_id === $this->user()->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'quoted_price' => ['required', 'numeric', 'min:0'],
            'vendor_notes' => ['nullable', 'string'],
        ];
    }
}
