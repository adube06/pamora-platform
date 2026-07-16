<?php

namespace App\Domains\Planning\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddTaskDependencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('task'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'depends_on_task_id' => [
                'required',
                'integer',
                Rule::exists('tasks', 'id')->where('occasion_id', $this->route('task')->occasion_id),
                Rule::notIn([$this->route('task')->id]),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'depends_on_task_id.not_in' => 'A Task cannot depend on itself.',
        ];
    }
}
