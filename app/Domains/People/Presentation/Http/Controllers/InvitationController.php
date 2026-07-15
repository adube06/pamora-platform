<?php

namespace App\Domains\People\Presentation\Http\Controllers;

use App\Domains\People\Application\Services\AcceptInvitationService;
use App\Domains\People\Domain\Models\Invitation;
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
                'responsibilities' => $invitation->responsibilities,
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
}
