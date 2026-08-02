<?php

namespace App\Domains\People\Presentation\Http\Controllers\Api;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Application\Services\AssignResponsibilitiesService;
use App\Domains\People\Application\Services\RemoveMemberService;
use App\Domains\People\Application\Services\UpdateMemberRoleService;
use App\Domains\People\Domain\Enums\InvitationStatus;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\People\Presentation\Http\Requests\AssignResponsibilitiesRequest;
use App\Domains\People\Presentation\Http\Requests\RemoveMemberRequest;
use App\Domains\People\Presentation\Http\Requests\UpdateMemberRoleRequest;
use App\Domains\People\Presentation\Http\Resources\InvitationResource;
use App\Domains\People\Presentation\Http\Resources\OccasionMemberResource;
use App\Domains\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MemberController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        Gate::authorize('view', $occasion);

        return ApiResponse::success([
            'members' => OccasionMemberResource::collection($occasion->members()->with('user')->get()),
            'pending_invitations' => InvitationResource::collection(
                $occasion->invitations()->where('status', InvitationStatus::Pending)->with('invitedBy')->get()
            ),
            'permissions' => [
                'can_invite' => $request->user()->can('invite-member', $occasion),
                'can_remove_member' => $request->user()->can('remove-member', $occasion),
                'can_assign_responsibilities' => $request->user()->can('assign-responsibilities', $occasion),
                'can_manage_permissions' => $request->user()->can('manage-permissions', $occasion),
                'can_transfer_ownership' => $request->user()->can('transferOwnership', $occasion),
                'can_reopen_rsvp' => $request->user()->id === $occasion->host_id,
            ],
        ]);
    }

    public function destroy(RemoveMemberRequest $request, OccasionMember $occasionMember, RemoveMemberService $service): JsonResponse
    {
        $service->handle($occasionMember, $request->user());

        return ApiResponse::success();
    }

    public function updateResponsibilities(AssignResponsibilitiesRequest $request, OccasionMember $occasionMember, AssignResponsibilitiesService $service): JsonResponse
    {
        $service->handle($occasionMember, $request->validated('responsibilities', []), $request->user());

        return ApiResponse::success();
    }

    public function updateRole(UpdateMemberRoleRequest $request, OccasionMember $occasionMember, UpdateMemberRoleService $service): JsonResponse
    {
        $service->handle($occasionMember, Role::from($request->validated('role')), $request->user());

        return ApiResponse::success();
    }
}
