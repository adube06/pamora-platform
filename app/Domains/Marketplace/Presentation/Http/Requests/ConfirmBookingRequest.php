<?php

namespace App\Domains\Marketplace\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('confirm-booking', $this->route('quotation')->occasion);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
