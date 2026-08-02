<?php

namespace App\Domains\People\Presentation\Http\Controllers\Api;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Application\Services\ReopenRsvpService;
use App\Domains\People\Application\Services\SubmitRsvpService;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\People\Presentation\Http\Requests\ReopenRsvpRequest;
use App\Domains\People\Presentation\Http\Requests\SubmitRsvpRequest;
use App\Domains\People\Presentation\Http\Resources\OccasionMemberResource;
use App\Domains\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class RsvpController
{
    public function store(SubmitRsvpRequest $request, Occasion $occasion, SubmitRsvpService $service): JsonResponse
    {
        $member = $occasion->memberFor($request->user());

        $member = $service->handle($member, $request->validated());

        return ApiResponse::success(new OccasionMemberResource($member->fresh()->load('user')));
    }

    public function reopen(ReopenRsvpRequest $request, OccasionMember $occasionMember, ReopenRsvpService $service): JsonResponse
    {
        $occasionMember = $service->handle($occasionMember, $request->user());

        return ApiResponse::success(new OccasionMemberResource($occasionMember->fresh()->load('user')));
    }
}
