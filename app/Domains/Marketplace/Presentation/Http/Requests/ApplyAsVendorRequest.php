<?php

namespace App\Domains\Marketplace\Presentation\Http\Requests;

use App\Domains\Marketplace\Domain\Enums\VendorCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ApplyAsVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => [new Enum(VendorCategory::class)],
            'service_areas' => ['nullable', 'array'],
            'service_areas.*' => ['string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:255'],
        ];
    }
}
