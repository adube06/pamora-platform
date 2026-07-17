<?php

namespace App\Domains\People\Presentation\Http\Requests;

use App\Domains\People\Domain\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateMemberRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-permissions', $this->route('occasionMember')->occasion);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', new Enum(Role::class), Rule::notIn(['host'])],
        ];
    }
}
