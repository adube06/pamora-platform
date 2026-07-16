<?php

namespace App\Domains\People\Presentation\Http\Controllers;

use App\Domains\People\Application\Services\AcceptInvitationService;
use App\Domains\People\Application\Services\DeclineInvitationService;
use App\Domains\People\Domain\Models\Invitation;
use App\Domains\People\Presentation\Http\Requests\DeclineInvitationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController
{
    public function show(Request $request, string $token): Response
    {
        $invitation = Invitation::where('token', $token)->with('occasion')->firstOrFail();

        return Inertia::render('Invitations/Accept', [
            'invitation' => [
                'token' => $invitation->token,
                'email' => $invitation->email,
                'status' => $invitation->status->value,
                'is_pending' => $invitation->isPending(),
                'role' => $invitation->role->value,
                'role_label' => $invitation->role->label(),
                'notes' => $invitation->notes,
                'occasion' => [
                    'title' => $invitation->occasion->title,
                    'type' => $invitation->occasion->type->value,
                ],
            ],
        ]);
    }

    public function accept(Request $request, string $token, AcceptInvitationService $service): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        $service->handle($invitation, $request->user());

        return redirect()
            ->route('occasions.committee', $invitation->occasion->slug)
            ->with('success', 'You have joined '.$invitation->occasion->title.'.');
    }

    public function decline(DeclineInvitationRequest $request, string $token, DeclineInvitationService $service): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        $service->handle($invitation);

        return redirect()
            ->route('invitations.show', $token)
            ->with('success', 'Invitation declined.');
    }
}
