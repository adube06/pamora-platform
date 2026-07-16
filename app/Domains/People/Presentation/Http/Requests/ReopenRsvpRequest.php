<?php

namespace App\Domains\People\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReopenRsvpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reopen-rsvp', $this->route('occasionMember'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
