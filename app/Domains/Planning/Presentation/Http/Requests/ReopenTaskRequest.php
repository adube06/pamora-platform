<?php

namespace App\Domains\Planning\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReopenTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reopen', $this->route('task'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
