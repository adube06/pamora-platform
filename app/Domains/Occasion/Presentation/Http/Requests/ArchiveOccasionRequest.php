<?php

namespace App\Domains\Occasion\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArchiveOccasionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('archive', $this->route('occasion'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
