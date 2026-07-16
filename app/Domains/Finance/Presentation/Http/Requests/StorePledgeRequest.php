<?php

namespace App\Domains\Finance\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePledgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('record-pledge', $this->route('occasion'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pledgor_name' => ['required', 'string', 'max:255'],
            'pledgor_phone' => ['nullable', 'string', 'max:32'],
            'amount' => ['required', 'numeric', 'min:1'],
            // Only the two "starting" statuses are settable at record time —
            // Cancelled/Expired only make sense as later transitions via the
            // update action.
            'status' => ['nullable', Rule::in(['pending', 'confirmed'])],
            'message' => ['nullable', 'string', 'max:1000'],
            'pledged_at' => ['required', 'date', Rule::date()->beforeOrEqual('today')],
        ];
    }
}
