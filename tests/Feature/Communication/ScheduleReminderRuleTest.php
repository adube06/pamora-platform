<?php

use App\Domains\Communication\Domain\Models\ReminderRule;
use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\TimelineEvent;
use App\Models\User;

it('lets an authorized member schedule a reminder for a timeline event', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $timelineEvent = TimelineEvent::factory()->create(['occasion_id' => $occasion->id]);

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/reminder-rules", [
        'timeline_event_id' => $timelineEvent->id,
        'offset_minutes' => 120,
    ]);

    $response->assertSessionHasNoErrors();

    expect(ReminderRule::where('timeline_event_id', $timelineEvent->id)->exists())->toBeTrue();
});

it('prevents a member without communication.schedule_reminder from scheduling a reminder', function () {
    $occasion = Occasion::factory()->create();
    $timelineEvent = TimelineEvent::factory()->create(['occasion_id' => $occasion->id]);
    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->post("/occasions/{$occasion->slug}/reminder-rules", [
            'timeline_event_id' => $timelineEvent->id,
            'offset_minutes' => 120,
        ])
        ->assertForbidden();

    expect(ReminderRule::where('timeline_event_id', $timelineEvent->id)->exists())->toBeFalse();
});

it('rejects a timeline event that belongs to a different occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $otherOccasionEvent = TimelineEvent::factory()->create();

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/reminder-rules", [
            'timeline_event_id' => $otherOccasionEvent->id,
            'offset_minutes' => 120,
        ])
        ->assertSessionHasErrors('timeline_event_id');
});

it('rejects scheduling a reminder rule on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $timelineEvent = TimelineEvent::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/reminder-rules", [
            'timeline_event_id' => $timelineEvent->id,
            'offset_minutes' => 120,
        ])
        ->assertSessionHasErrors('occasion');

    expect(ReminderRule::where('timeline_event_id', $timelineEvent->id)->exists())->toBeFalse();
});
