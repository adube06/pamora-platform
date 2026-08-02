<?php

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Checklist;
use App\Domains\Planning\Domain\Models\Milestone;
use App\Domains\Planning\Domain\Models\TimelineEvent;
use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function occasionWithHostForPlanningResources(): array
{
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'created_by' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    return [$occasion, $host];
}

// --- Checklists ---

it('lists checklists via the API', function () {
    [$occasion, $host] = occasionWithHostForPlanningResources();
    Checklist::factory()->create(['occasion_id' => $occasion->id, 'name' => 'Catering']);

    Sanctum::actingAs($host);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/checklists");

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.name', 'Catering');
});

it('creates a checklist via the API', function () {
    [$occasion, $host] = occasionWithHostForPlanningResources();
    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/checklists", ['name' => 'Decorations']);

    $response->assertCreated();
    $response->assertJsonPath('data.name', 'Decorations');
});

it('rejects creating a checklist from a member without planning.manage_checklist', function () {
    [$occasion] = occasionWithHostForPlanningResources();
    $guest = User::factory()->create();
    OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $guest->id, 'permissions' => []]);

    Sanctum::actingAs($guest);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/checklists", ['name' => 'Should not exist']);

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

// --- Milestones ---

it('lists milestones with linked tasks via the API', function () {
    [$occasion, $host] = occasionWithHostForPlanningResources();
    $milestone = Milestone::factory()->create(['occasion_id' => $occasion->id, 'name' => 'Venue Confirmed']);

    Sanctum::actingAs($host);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/milestones");

    $response->assertOk();
    $response->assertJsonPath('data.0.name', 'Venue Confirmed');
    $response->assertJsonPath('data.0.is_achieved', false);
});

it('creates a milestone via the API and logs an audit entry', function () {
    [$occasion, $host] = occasionWithHostForPlanningResources();
    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/milestones", ['name' => 'Budget Approved']);

    $response->assertCreated();
    $response->assertJsonPath('data.name', 'Budget Approved');

    $milestone = Milestone::firstWhere('name', 'Budget Approved');

    expect(ActivityLog::where('action', 'planning.milestone_created')
        ->where('subject_id', $milestone->id)
        ->count())->toBe(1);
});

it('rejects creating a milestone from a member without planning.manage_milestone', function () {
    [$occasion] = occasionWithHostForPlanningResources();
    $guest = User::factory()->create();
    OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $guest->id, 'permissions' => []]);

    Sanctum::actingAs($guest);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/milestones", ['name' => 'Should not exist']);

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

// --- Timeline Events ---

it('lists timeline events ordered by scheduled_at via the API', function () {
    [$occasion, $host] = occasionWithHostForPlanningResources();
    TimelineEvent::factory()->create(['occasion_id' => $occasion->id, 'name' => 'Later', 'scheduled_at' => now()->addDays(10)]);
    TimelineEvent::factory()->create(['occasion_id' => $occasion->id, 'name' => 'Sooner', 'scheduled_at' => now()->addDays(2)]);

    Sanctum::actingAs($host);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/timeline-events");

    $response->assertOk();
    $response->assertJsonPath('data.0.name', 'Sooner');
    $response->assertJsonPath('data.1.name', 'Later');
});

it('schedules a timeline event via the API', function () {
    [$occasion, $host] = occasionWithHostForPlanningResources();
    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/timeline-events", [
        'name' => 'Committee Meeting',
        'scheduled_at' => now()->addWeek()->toDateTimeString(),
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.name', 'Committee Meeting');
});

it('rejects scheduling a timeline event without a date', function () {
    [$occasion, $host] = occasionWithHostForPlanningResources();
    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/timeline-events", ['name' => 'Missing date']);

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});
