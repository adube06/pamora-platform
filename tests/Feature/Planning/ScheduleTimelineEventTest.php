<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\TimelineEvent;
use App\Models\User;

it('lets an authorized member schedule a timeline event', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/timeline-events", [
        'name' => 'Committee Meeting',
        'scheduled_at' => now()->addWeek()->toDateTimeString(),
    ]);

    $response->assertSessionHasNoErrors();

    expect(TimelineEvent::firstWhere('name', 'Committee Meeting'))->not->toBeNull();
});

it('prevents a member without planning.manage_timeline from scheduling a timeline event', function () {
    $occasion = Occasion::factory()->create();
    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->post("/occasions/{$occasion->slug}/timeline-events", [
            'name' => 'Should not be created',
            'scheduled_at' => now()->addWeek()->toDateTimeString(),
        ])
        ->assertForbidden();

    expect(TimelineEvent::where('name', 'Should not be created')->exists())->toBeFalse();
});

it('rejects scheduling a timeline event on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/timeline-events", [
            'name' => 'Should not be created',
            'scheduled_at' => now()->addWeek()->toDateTimeString(),
        ])
        ->assertSessionHasErrors('occasion');

    expect(TimelineEvent::where('name', 'Should not be created')->exists())->toBeFalse();
});
