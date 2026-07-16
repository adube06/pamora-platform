<?php

namespace App\Domains\People\Presentation\Http\Controllers;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Application\Services\InviteMemberService;
use App\Domains\People\Application\Services\RemoveMemberService;
use App\Domains\People\Domain\Enums\InvitationStatus;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\People\Presentation\Http\Requests\InviteMemberRequest;
use App\Domains\People\Presentation\Http\Requests\RemoveMemberRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommitteeController
{
    public function index(Request $request, Occasion $occasion): Response
    {
        $request->user()->can('view', $occasion) || abort(403);

        return Inertia::render('Occasions/Committee', [
            'occasion' => $occasion,
            'members' => $occasion->members()->with('user:id,name,email')->get(),
            'pendingInvitations' => $occasion->invitations()->where('status', InvitationStatus::Pending)->get(),
            'canInvite' => $request->user()->can('invite-member', $occasion),
            'roles' => collect(Role::cases())
                ->reject(fn (Role $role) => $role === Role::Host)
                ->map(fn (Role $role) => ['value' => $role->value, 'label' => $role->label()])
                ->values(),
            'myMembership' => $occasion->memberFor($request->user()),
            'canReopenRsvp' => $request->user()->id === $occasion->host_id,
            'canRemoveMember' => $request->user()->can('remove-member', $occasion),
            'canTransferOwnership' => $request->user()->can('transferOwnership', $occasion),
        ]);
    }

    public function store(InviteMemberRequest $request, Occasion $occasion, InviteMemberService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Invitation sent.');
    }

    public function destroy(RemoveMemberRequest $request, OccasionMember $occasionMember, RemoveMemberService $service): RedirectResponse
    {
        $service->handle($occasionMember, $request->user());

        return back()->with('success', 'Member removed.');
    }
}
