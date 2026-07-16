<?php

use App\Domains\Communication\Domain\Models\Notification;
use App\Domains\Communication\Domain\Models\ReminderRule;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

it('triggers a due reminder rule and notifies its creator', function () {
    $creator = User::factory()->create();
    $occasion = Occasion::factory()->create();
    $timelineEvent = TimelineEvent::factory()->create([
        'occasion_id' => $occasion->id,
        'scheduled_at' => now()->addMinutes(30),
    ]);
    $rule = ReminderRule::factory()->create([
        'occasion_id' => $occasion->id,
        'timeline_event_id' => $timelineEvent->id,
        'offset_minutes' => 60,
        'created_by' => $creator->id,
    ]);

    Artisan::call('reminders:dispatch');

    expect($rule->fresh()->triggered_at)->not->toBeNull();

    $notification = Notification::where('user_id', $creator->id)->where('type', 'reminder_triggered')->first();

    expect($notification)->not->toBeNull()
        ->and($notification->subject_type)->toBe('TimelineEvent')
        ->and($notification->subject_id)->toBe($timelineEvent->id);
});

it('does not trigger a reminder rule that is not yet due', function () {
    $occasion = Occasion::factory()->create();
    $timelineEvent = TimelineEvent::factory()->create([
        'occasion_id' => $occasion->id,
        'scheduled_at' => now()->addDays(10),
    ]);
    $rule = ReminderRule::factory()->create([
        'occasion_id' => $occasion->id,
        'timeline_event_id' => $timelineEvent->id,
        'offset_minutes' => 120,
    ]);

    Artisan::call('reminders:dispatch');

    expect($rule->fresh()->triggered_at)->toBeNull();
});

it('does not fire an already-triggered reminder rule twice', function () {
    $creator = User::factory()->create();
    $occasion = Occasion::factory()->create();
    $timelineEvent = TimelineEvent::factory()->create([
        'occasion_id' => $occasion->id,
        'scheduled_at' => now()->addMinutes(30),
    ]);
    $rule = ReminderRule::factory()->create([
        'occasion_id' => $occasion->id,
        'timeline_event_id' => $timelineEvent->id,
        'offset_minutes' => 60,
        'created_by' => $creator->id,
        'triggered_at' => now()->subMinute(),
    ]);

    Artisan::call('reminders:dispatch');

    expect(Notification::where('user_id', $creator->id)->where('type', 'reminder_triggered')->count())->toBe(0);
});
