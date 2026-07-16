<?php

use App\Domains\Insights\Application\Services\GetParticipationService;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\InvitationStatus;
use App\Domains\People\Domain\Models\Invitation;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;

it('returns null rates with no invitations or members yet', function () {
    $occasion = Occasion::factory()->create();

    $participation = app(GetParticipationService::class)->handle($occasion);

    expect($participation['invitation_acceptance_rate'])->toBeNull()
        ->and($participation['total_invitations'])->toBe(0)
        ->and($participation['active_member_count'])->toBe(0)
        ->and($participation['rsvp_completion_rate'])->toBeNull()
        ->and($participation['task_ownership'])->toBe([]);
});

it('computes the invitation acceptance rate', function () {
    $occasion = Occasion::factory()->create();
    Invitation::factory()->create(['occasion_id' => $occasion->id, 'status' => InvitationStatus::Accepted]);
    Invitation::factory()->create(['occasion_id' => $occasion->id, 'status' => InvitationStatus::Accepted]);
    Invitation::factory()->create(['occasion_id' => $occasion->id, 'status' => InvitationStatus::Pending]);
    Invitation::factory()->create(['occasion_id' => $occasion->id, 'status' => InvitationStatus::Declined]);

    $participation = app(GetParticipationService::class)->handle($occasion);

    expect($participation['total_invitations'])->toBe(4)
        ->and($participation['invitation_acceptance_rate'])->toBe(50.0);
});

it('computes the rsvp completion rate among active members only', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $host->id,
        'rsvp_status' => 'attending',
    ]);
    OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'rsvp_status' => null]);

    $participation = app(GetParticipationService::class)->handle($occasion);

    expect($participation['active_member_count'])->toBe(2)
        ->and($participation['rsvp_completion_rate'])->toBe(50.0);
});

it('lists task ownership sorted by count, excluding cancelled and unassigned tasks', function () {
    $occasion = Occasion::factory()->create();
    $memberUser = User::factory()->create(['name' => 'Amina Hassan']);
    $member = OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $memberUser->id]);

    Task::factory()->count(2)->create(['occasion_id' => $occasion->id, 'assignee_id' => $member->id, 'status' => TaskStatus::Open]);
    Task::factory()->create(['occasion_id' => $occasion->id, 'assignee_id' => $member->id, 'status' => TaskStatus::Cancelled]);
    Task::factory()->create(['occasion_id' => $occasion->id, 'assignee_id' => null, 'status' => TaskStatus::Open]);

    $participation = app(GetParticipationService::class)->handle($occasion);

    expect($participation['task_ownership'])->toHaveCount(1)
        ->and($participation['task_ownership'][0]['member_name'])->toBe('Amina Hassan')
        ->and($participation['task_ownership'][0]['task_count'])->toBe(2);
});
