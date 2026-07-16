<?php

namespace App\Domains\Occasion\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferOwnershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transferOwnership', $this->route('occasion'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'member_uuid' => [
                'required',
                'string',
                Rule::exists('occasion_members', 'uuid')->where('occasion_id', $this->route('occasion')->id),
            ],
        ];
    }
}
