<?php

namespace App\Domains\Media\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MoveMediaAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit-media-metadata', $this->route('mediaAsset'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'album_id' => [
                'nullable',
                'integer',
                Rule::exists('albums', 'id')->where('occasion_id', $this->route('mediaAsset')->occasion_id),
            ],
            'task_id' => [
                'nullable',
                'integer',
                Rule::exists('tasks', 'id')->where('occasion_id', $this->route('mediaAsset')->occasion_id),
            ],
            'expense_id' => [
                'nullable',
                'integer',
                Rule::exists('expenses', 'id')->where('occasion_id', $this->route('mediaAsset')->occasion_id),
            ],
            'announcement_id' => [
                'nullable',
                'integer',
                Rule::exists('announcements', 'id')->where('occasion_id', $this->route('mediaAsset')->occasion_id),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $targetsProvided = collect(['album_id', 'task_id', 'expense_id', 'announcement_id'])
                ->filter(fn (string $field) => $this->filled($field));

            if ($targetsProvided->count() > 1) {
                $validator->errors()->add('album_id', 'A Media Asset can only be moved to one place at a time.');
            }
        });
    }
}
