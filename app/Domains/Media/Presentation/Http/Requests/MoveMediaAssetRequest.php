<?php

namespace App\Domains\Media\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        ];
    }
}
