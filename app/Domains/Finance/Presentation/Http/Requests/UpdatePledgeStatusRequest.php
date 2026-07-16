<?php

namespace App\Domains\Finance\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePledgeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pledge = $this->route('pledge');
        $occasion = $this->route('occasion');

        return $pledge->occasion_id === $occasion->id
            && $this->user()->can('record-pledge', $occasion);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['pending', 'confirmed', 'cancelled', 'expired'])],
        ];
    }
}
