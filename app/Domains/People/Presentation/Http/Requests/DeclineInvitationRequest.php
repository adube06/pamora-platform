<?php

namespace App\Domains\People\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeclineInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Declining an invitation is unauthenticated and unconditional — the
        // token itself is the credential, mirroring invitations.show being
        // reachable while logged out. No permission gate applies here.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
