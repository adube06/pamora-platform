<?php

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
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
