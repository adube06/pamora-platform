<?php

namespace App\Domains\Media\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('upload-media', $this->route('occasion'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm,pdf,doc,docx,xls,xlsx,txt',
                'max:20480',
            ],
            'visibility' => ['nullable', Rule::in(['occasion_members', 'private'])],
        ];
    }
}
