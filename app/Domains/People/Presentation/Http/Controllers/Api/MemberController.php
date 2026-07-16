<?php

namespace App\Domains\People\Presentation\Http\Controllers\Api;

use App\Domains\People\Application\Services\RemoveMemberService;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\People\Presentation\Http\Requests\RemoveMemberRequest;
use Illuminate\Http\JsonResponse;

class MemberController
{
    public function destroy(RemoveMemberRequest $request, OccasionMember $occasionMember, RemoveMemberService $service): JsonResponse
    {
        $service->handle($occasionMember, $request->user());

        return response()->json(['success' => true]);
    }
}
