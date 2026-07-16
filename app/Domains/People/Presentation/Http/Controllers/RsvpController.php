<?php

namespace App\Domains\People\Presentation\Http\Controllers;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Application\Services\ReopenRsvpService;
use App\Domains\People\Application\Services\SubmitRsvpService;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\People\Presentation\Http\Requests\ReopenRsvpRequest;
use App\Domains\People\Presentation\Http\Requests\SubmitRsvpRequest;
use Illuminate\Http\RedirectResponse;

class RsvpController
{
    public function store(SubmitRsvpRequest $request, Occasion $occasion, SubmitRsvpService $service): RedirectResponse
    {
        $member = $occasion->memberFor($request->user());

        $service->handle($member, $request->validated());

        return back()->with('success', 'RSVP submitted.');
    }

    public function reopen(ReopenRsvpRequest $request, OccasionMember $occasionMember, ReopenRsvpService $service): RedirectResponse
    {
        $service->handle($occasionMember, $request->user());

        return back()->with('success', 'RSVP reopened.');
    }
}
