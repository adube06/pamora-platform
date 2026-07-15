<?php

namespace App\Domains\Planning\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assign', $this->route('task'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'assignee_id' => ['required', 'integer', 'exists:occasion_members,id'],
        ];
    }
}
