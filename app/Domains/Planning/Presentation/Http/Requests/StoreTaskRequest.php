<?php

namespace App\Domains\Planning\Presentation\Http\Requests;

use App\Domains\Planning\Domain\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-task', $this->route('occasion'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', new Enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date'],
            'checklist_id' => [
                'nullable',
                'integer',
                Rule::exists('checklists', 'id')->where('occasion_id', $this->route('occasion')->id),
            ],
        ];
    }
}
