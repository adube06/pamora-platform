<?php

namespace App\Domains\Planning\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-checklist', $this->route('occasion'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
