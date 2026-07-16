<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Milestone;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;

it('lets an authorized member create a milestone with linked tasks', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/milestones", [
        'name' => 'Venue Confirmed',
        'task_ids' => [$task->id],
    ]);

    $response->assertSessionHasNoErrors();

    $milestone = Milestone::firstWhere('name', 'Venue Confirmed');
    expect($milestone)->not->toBeNull()
        ->and($milestone->tasks()->pluck('tasks.id')->all())->toBe([$task->id]);
});

it('rejects a task_id belonging to a different occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $foreignTask = Task::factory()->create();

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/milestones", [
            'name' => 'Should not be created',
            'task_ids' => [$foreignTask->id],
        ])
        ->assertSessionHasErrors('task_ids.0');

    expect(Milestone::where('name', 'Should not be created')->exists())->toBeFalse();
});

it('prevents a member without planning.manage_milestone from creating a milestone', function () {
    $occasion = Occasion::factory()->create();
    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->post("/occasions/{$occasion->slug}/milestones", ['name' => 'Should not be created'])
        ->assertForbidden();

    expect(Milestone::where('name', 'Should not be created')->exists())->toBeFalse();
});

it('rejects creating a milestone on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/milestones", ['name' => 'Should not be created'])
        ->assertSessionHasErrors('occasion');

    expect(Milestone::where('name', 'Should not be created')->exists())->toBeFalse();
});
