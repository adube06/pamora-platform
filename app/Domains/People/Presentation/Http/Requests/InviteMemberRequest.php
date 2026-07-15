<?php

namespace App\Domains\People\Presentation\Http\Requests;

use App\Domains\People\Domain\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // Host is assigned automatically on Occasion creation
            // (CreateHostMembershipService) — it is never an invitable Role.
            'role' => ['required', Rule::in($this->invitableRoles())],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return list<string>
     */
    private function invitableRoles(): array
    {
        return array_map(
            fn (Role $role) => $role->value,
            array_filter(Role::cases(), fn (Role $role) => $role !== Role::Host)
        );
    }
}
