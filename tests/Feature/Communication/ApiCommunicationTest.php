<?php

use App\Domains\Communication\Domain\Models\Announcement;
use App\Domains\Communication\Domain\Models\Notification;
use App\Domains\Communication\Domain\Models\ReminderRule;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\TimelineEvent;
use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function occasionWithHostCommunication(): array
{
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'created_by' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    return [$occasion, $host];
}

// --- Announcements ---

it('publishes an announcement via the API with the correct default audience and status', function () {
    [$occasion, $host] = occasionWithHostCommunication();
    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/announcements", [
        'title' => 'Venue Update',
        'message' => 'The ceremony has moved to the garden hall.',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.audience', 'all_members');
    $response->assertJsonPath('data.status', 'published');
});

it('rejects publishing from a member without communication.publish_announcement', function () {
    [$occasion] = occasionWithHostCommunication();
    $guest = User::factory()->create();
    OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $guest->id, 'permissions' => []]);

    Sanctum::actingAs($guest);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/announcements", [
        'title' => 'Should not exist',
        'message' => 'x',
    ]);

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

it('lists announcements via the API', function () {
    [$occasion, $host] = occasionWithHostCommunication();
    Announcement::factory()->create(['occasion_id' => $occasion->id, 'title' => 'Existing']);

    Sanctum::actingAs($host);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/announcements");

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('rejects listing announcements for a non-member', function () {
    [$occasion] = occasionWithHostCommunication();
    Sanctum::actingAs(User::factory()->create());

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/announcements");

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

it('logs an audit entry when an announcement is published via the API', function () {
    [$occasion, $host] = occasionWithHostCommunication();
    Sanctum::actingAs($host);

    $this->postJson("/api/v1/occasions/{$occasion->slug}/announcements", [
        'title' => 'Audited Announcement',
        'message' => 'x',
    ]);

    $announcement = Announcement::firstWhere('title', 'Audited Announcement');

    expect(ActivityLog::where('action', 'communication.announcement_published')
        ->where('subject_id', $announcement->id)
        ->count())->toBe(1);
});

// --- Reminder Rules ---

it('lists reminder rules via the API', function () {
    [$occasion, $host] = occasionWithHostCommunication();
    $timelineEvent = TimelineEvent::factory()->create(['occasion_id' => $occasion->id]);
    ReminderRule::factory()->create(['occasion_id' => $occasion->id, 'timeline_event_id' => $timelineEvent->id]);

    Sanctum::actingAs($host);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/reminder-rules");

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.timeline_event.id', $timelineEvent->uuid);
});

it('schedules a reminder rule via the API', function () {
    [$occasion, $host] = occasionWithHostCommunication();
    $timelineEvent = TimelineEvent::factory()->create(['occasion_id' => $occasion->id]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/reminder-rules", [
        'timeline_event_id' => $timelineEvent->id,
        'offset_minutes' => 1440,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.offset_minutes', 1440);
});

it('rejects an unsupported reminder offset via the API', function () {
    [$occasion, $host] = occasionWithHostCommunication();
    $timelineEvent = TimelineEvent::factory()->create(['occasion_id' => $occasion->id]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/reminder-rules", [
        'timeline_event_id' => $timelineEvent->id,
        'offset_minutes' => 30,
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

// --- Notifications ---

it('lists only the authenticated user\'s own notifications via the API', function () {
    $user = User::factory()->create();
    Notification::factory()->create(['user_id' => $user->id, 'title' => 'Mine']);
    Notification::factory()->create(['title' => 'Not mine']);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/notifications');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.title', 'Mine');
});

it('marks a notification as read via the API', function () {
    $user = User::factory()->create();
    $notification = Notification::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/notifications/{$notification->uuid}/read");

    $response->assertOk();
    expect($response->json('data.read_at'))->not->toBeNull();
});

it('rejects marking another user\'s notification as read via the API', function () {
    $notification = Notification::factory()->create();
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson("/api/v1/notifications/{$notification->uuid}/read");

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

// --- Preferences ---

it('updates notification preferences via the API', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/v1/preferences', ['task_assigned' => false]);

    $response->assertOk();
    $response->assertJsonPath('data.notification_preferences.task_assigned', false);
});
