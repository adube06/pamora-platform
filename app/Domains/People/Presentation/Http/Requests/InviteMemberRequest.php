<?php

namespace App\Domains\People\Presentation\Http\Requests;

use App\Domains\People\Domain\Enums\Responsibility;
use App\Domains\Shared\Domain\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class InviteMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('invite-member', $this->route('occasion'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'responsibilities' => ['sometimes', 'array'],
            'responsibilities.*' => [new Enum(Responsibility::class)],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [new Enum(Permission::class)],
        ];
    }
}
