<?php

namespace App\Domains\People\Presentation\Http\Controllers\Api;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Application\Services\ReopenRsvpService;
use App\Domains\People\Application\Services\SubmitRsvpService;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\People\Presentation\Http\Requests\ReopenRsvpRequest;
use App\Domains\People\Presentation\Http\Requests\SubmitRsvpRequest;
use Illuminate\Http\JsonResponse;

class RsvpController
{
    public function store(SubmitRsvpRequest $request, Occasion $occasion, SubmitRsvpService $service): JsonResponse
    {
        $member = $occasion->memberFor($request->user());

        $service->handle($member, $request->validated());

        return response()->json(['success' => true, 'data' => $member->fresh()]);
    }

    public function reopen(ReopenRsvpRequest $request, OccasionMember $occasionMember, ReopenRsvpService $service): JsonResponse
    {
        $service->handle($occasionMember, $request->user());

        return response()->json(['success' => true, 'data' => $occasionMember->fresh()]);
    }
}
