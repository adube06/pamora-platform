<?php

namespace App\Domains\Marketplace\Presentation\Http\Requests;

use App\Domains\Marketplace\Domain\Enums\PricingModel;
use App\Domains\Marketplace\Domain\Enums\VendorCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PublishServiceRequest extends FormRequest
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
            'category' => ['required', new Enum(VendorCategory::class)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'pricing_model' => ['required', new Enum(PricingModel::class)],
            'price' => ['required_if:pricing_model,fixed', 'nullable', 'numeric', 'min:0'],
            'estimated_duration' => ['nullable', 'string', 'max:255'],
        ];
    }
}
