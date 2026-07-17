<?php

namespace App\Domains\People\Presentation\Http\Controllers\Api;

use App\Domains\People\Application\Services\AssignResponsibilitiesService;
use App\Domains\People\Application\Services\RemoveMemberService;
use App\Domains\People\Application\Services\UpdateMemberRoleService;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\People\Presentation\Http\Requests\AssignResponsibilitiesRequest;
use App\Domains\People\Presentation\Http\Requests\RemoveMemberRequest;
use App\Domains\People\Presentation\Http\Requests\UpdateMemberRoleRequest;
use Illuminate\Http\JsonResponse;

class MemberController
{
    public function destroy(RemoveMemberRequest $request, OccasionMember $occasionMember, RemoveMemberService $service): JsonResponse
    {
        $service->handle($occasionMember, $request->user());

        return response()->json(['success' => true]);
    }

    public function updateResponsibilities(AssignResponsibilitiesRequest $request, OccasionMember $occasionMember, AssignResponsibilitiesService $service): JsonResponse
    {
        $service->handle($occasionMember, $request->validated('responsibilities', []), $request->user());

        return response()->json(['success' => true]);
    }

    public function updateRole(UpdateMemberRoleRequest $request, OccasionMember $occasionMember, UpdateMemberRoleService $service): JsonResponse
    {
        $service->handle($occasionMember, Role::from($request->validated('role')), $request->user());

        return response()->json(['success' => true]);
    }
}
