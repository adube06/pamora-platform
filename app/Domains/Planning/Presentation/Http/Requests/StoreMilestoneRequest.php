<?php

namespace App\Domains\Planning\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-milestone', $this->route('occasion'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'task_ids' => ['nullable', 'array'],
            'task_ids.*' => [
                'integer',
                Rule::exists('tasks', 'id')->where('occasion_id', $this->route('occasion')->id),
            ],
        ];
    }
}
