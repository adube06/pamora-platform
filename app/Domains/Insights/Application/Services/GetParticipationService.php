<?php

namespace App\Domains\Insights\Application\Services;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\InvitationStatus;
use App\Domains\People\Domain\Enums\OccasionMemberStatus;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Enums\TaskStatus;

/**
 * ADR-008 (Analytics Is Read-Only): computed fresh from Invitation,
 * OccasionMember, and Task rows every call, nothing stored — same shape
 * as GetReadinessScoreService and GetTaskProgressService.
 *
 * @phpstan-type TaskOwnership array{member_name: string, task_count: int}
 * @phpstan-type ParticipationSummary array{
 *     invitation_acceptance_rate: ?float,
 *     total_invitations: int,
 *     rsvp_completion_rate: ?float,
 *     active_member_count: int,
 *     task_ownership: list<TaskOwnership>,
 * }
 */
class GetParticipationService
{
    /**
     * @return ParticipationSummary
     */
    public function handle(Occasion $occasion): array
    {
        $totalInvitations = $occasion->invitations()->count();
        $acceptedInvitations = $occasion->invitations()->where('status', InvitationStatus::Accepted)->count();

        $activeMembers = $occasion->members()->where('status', OccasionMemberStatus::Active);
        $activeMemberCount = (clone $activeMembers)->count();
        $rsvpResponded = (clone $activeMembers)->whereNotNull('rsvp_status')->count();

        return [
            'invitation_acceptance_rate' => $totalInvitations > 0
                ? round(($acceptedInvitations / $totalInvitations) * 100, 1)
                : null,
            'total_invitations' => $totalInvitations,
            'rsvp_completion_rate' => $activeMemberCount > 0
                ? round(($rsvpResponded / $activeMemberCount) * 100, 1)
                : null,
            'active_member_count' => $activeMemberCount,
            'task_ownership' => $this->taskOwnership($occasion),
        ];
    }

    /**
     * @return list<TaskOwnership>
     */
    private function taskOwnership(Occasion $occasion): array
    {
        $counts = $occasion->tasks()
            ->whereNotNull('assignee_id')
            ->where('status', '!=', TaskStatus::Cancelled)
            ->selectRaw('assignee_id, count(*) as aggregate')
            ->groupBy('assignee_id')
            ->orderByDesc('aggregate')
            ->pluck('aggregate', 'assignee_id');

        if ($counts->isEmpty()) {
            return [];
        }

        $members = OccasionMember::with('user:id,name')->whereIn('id', $counts->keys())->get()->keyBy('id');

        return $counts->map(fn (int $count, int $assigneeId) => [
            'member_name' => $members->get($assigneeId)?->user->name ?? 'Unknown',
            'task_count' => $count,
        ])->values()->all();
    }
}
