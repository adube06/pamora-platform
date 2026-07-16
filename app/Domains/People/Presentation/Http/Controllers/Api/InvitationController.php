<?php

namespace App\Domains\People\Presentation\Http\Controllers\Api;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Application\Services\AcceptInvitationService;
use App\Domains\People\Application\Services\DeclineInvitationService;
use App\Domains\People\Application\Services\InviteMemberService;
use App\Domains\People\Domain\Models\Invitation;
use App\Domains\People\Presentation\Http\Requests\InviteMemberRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationController
{
    public function store(InviteMemberRequest $request, Occasion $occasion, InviteMemberService $service): JsonResponse
    {
        $invitation = $service->handle($occasion, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $invitation->token,
                'email' => $invitation->email,
                'status' => $invitation->status->value,
                'expires_at' => $invitation->expires_at->toIso8601String(),
            ],
        ], 201);
    }

    public function accept(Request $request, string $token, AcceptInvitationService $service): JsonResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        $member = $service->handle($invitation, $request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $member->uuid,
                'occasion_id' => $invitation->occasion->uuid,
                'status' => $member->status->value,
            ],
        ]);
    }

    public function decline(string $token, DeclineInvitationService $service): JsonResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        $invitation = $service->handle($invitation);

        return response()->json([
            'success' => true,
            'data' => ['status' => $invitation->status->value],
        ]);
    }
}
