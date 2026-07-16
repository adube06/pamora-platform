<?php

namespace App\Domains\Communication\Presentation\Http\Requests;

use App\Domains\Communication\Domain\Models\Notification;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Managing your own notification preferences is Self-scoped (Permission
        // Catalog: communication.manage_preferences, Scope: Self) — a plain
        // authenticated check, not an Occasion-member permission grant, since
        // a user can only ever update their own record.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (array_keys(Notification::TYPES) as $type) {
            $rules[$type] = ['nullable', 'boolean'];
        }

        return $rules;
    }
}
