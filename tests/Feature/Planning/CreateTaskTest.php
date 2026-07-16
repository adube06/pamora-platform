<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Checklist;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;

it('lets an authorized member create a task', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/tasks", [
        'title' => 'Book the venue',
        'priority' => 'high',
    ]);

    $response->assertSessionHasNoErrors();

    expect(Task::firstWhere('title', 'Book the venue'))->not->toBeNull();
});

it('prevents a member without planning.create_task from creating a task', function () {
    $occasion = Occasion::factory()->create();
    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->post("/occasions/{$occasion->slug}/tasks", ['title' => 'Should not be created'])
        ->assertForbidden();

    expect(Task::where('title', 'Should not be created')->exists())->toBeFalse();
});

it('links a task to a checklist from the same occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $checklist = Checklist::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/tasks", [
        'title' => 'Book caterer',
        'checklist_id' => $checklist->id,
    ])->assertSessionHasNoErrors();

    $task = Task::firstWhere('title', 'Book caterer');
    expect($task->checklist_id)->toBe($checklist->id);
});

it('rejects a checklist_id belonging to a different occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $foreignChecklist = Checklist::factory()->create();

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/tasks", [
            'title' => 'Should not be created',
            'checklist_id' => $foreignChecklist->id,
        ])
        ->assertSessionHasErrors('checklist_id');

    expect(Task::where('title', 'Should not be created')->exists())->toBeFalse();
});

it('rejects creating a task on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/tasks", ['title' => 'Should not be created'])
        ->assertSessionHasErrors('occasion');

    expect(Task::where('title', 'Should not be created')->exists())->toBeFalse();
});
