<?php

namespace App\Domains\People\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitRsvpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('occasion')->memberFor($this->user()) !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rsvp_status' => ['required', Rule::in(['attending', 'not_attending', 'maybe'])],
            'guest_count' => ['nullable', 'integer', 'min:0'],
            'rsvp_message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
