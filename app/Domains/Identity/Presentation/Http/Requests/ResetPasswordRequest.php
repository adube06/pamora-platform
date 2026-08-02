<?php

namespace App\Domains\Identity\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            // Password::defaults() matches RegisterRequest — a reset must never be able
            // to set a weaker password than registration allows.
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
