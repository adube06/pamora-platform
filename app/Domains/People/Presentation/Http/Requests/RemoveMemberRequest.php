<?php

namespace App\Domains\People\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('remove-member', $this->route('occasionMember')->occasion);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
