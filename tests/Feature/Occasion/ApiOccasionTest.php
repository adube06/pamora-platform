<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('lists only occasions the authenticated user is a member of', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $mine = Occasion::factory()->create();
    OccasionMember::factory()->host()->create(['occasion_id' => $mine->id, 'user_id' => $user->id]);

    $someoneElses = Occasion::factory()->create();

    $response = $this->getJson('/api/v1/occasions');

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.slug', $mine->slug);
    expect(collect($response->json('data'))->pluck('slug'))->not->toContain($someoneElses->slug);
});

it('creates an occasion via the API and auto-assigns the host', function () {
    $host = User::factory()->create();
    Sanctum::actingAs($host);

    $response = $this->postJson('/api/v1/occasions', [
        'title' => "Amina & John's Wedding",
        'type' => 'wedding',
        'primary_date' => now()->addMonth()->toDateString(),
    ]);

    $response->assertCreated();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.title', "Amina & John's Wedding");

    $occasion = Occasion::firstWhere('title', "Amina & John's Wedding");
    $member = $occasion->memberFor($host);

    expect($member)->not->toBeNull()
        ->and($member->status->value)->toBe('active')
        ->and($member->hasPermission(Permission::OccasionEdit))->toBeTrue();
});

it('rejects creating an occasion with missing required fields', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/v1/occasions', []);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
    expect($response->json('error.fields'))->toHaveKeys(['title', 'type']);
});

it('logs an audit entry when an occasion is created via the API', function () {
    $host = User::factory()->create();
    Sanctum::actingAs($host);

    $this->postJson('/api/v1/occasions', ['title' => 'Amina\'s Birthday', 'type' => 'birthday']);

    $occasion = Occasion::firstWhere('title', "Amina's Birthday");

    expect(ActivityLog::where('action', 'occasion.created')
        ->where('subject_id', $occasion->id)
        ->count())->toBe(1);
});

it('lets a member view an occasion they belong to', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    Sanctum::actingAs($host);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}");

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.slug', $occasion->slug);
});

it('rejects a non-member from viewing an occasion with a forbidden envelope', function () {
    $occasion = Occasion::factory()->create();
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $occasion->host_id]);

    Sanctum::actingAs(User::factory()->create());

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}");

    $response->assertStatus(403);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

it('returns a not-found envelope for a nonexistent occasion', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->getJson('/api/v1/occasions/does-not-exist');

    $response->assertStatus(404);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('error.code', 'NOT_FOUND');
});

it('lets the host edit occasion details via the API', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'title' => 'Old Title']);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    Sanctum::actingAs($host);

    $response = $this->patchJson("/api/v1/occasions/{$occasion->slug}", [
        'title' => 'New Title',
        'type' => $occasion->type->value,
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.title', 'New Title');
    expect($occasion->fresh()->title)->toBe('New Title');
});

it('rejects updates from a member without occasion.edit with a forbidden envelope', function () {
    $occasion = Occasion::factory()->create();
    $observer = User::factory()->create();
    OccasionMember::factory()->role(Role::Observer)->create(['occasion_id' => $occasion->id, 'user_id' => $observer->id]);

    Sanctum::actingAs($observer);

    $response = $this->patchJson("/api/v1/occasions/{$occasion->slug}", [
        'title' => 'Hacked Title',
        'type' => $occasion->type->value,
    ]);

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
    expect($occasion->fresh()->title)->not->toBe('Hacked Title');
});

it('rejects editing an archived occasion with a validation envelope', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    Sanctum::actingAs($host);

    $response = $this->patchJson("/api/v1/occasions/{$occasion->slug}", [
        'title' => 'New Title',
        'type' => $occasion->type->value,
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
    expect($response->json('error.fields.status'))->not->toBeEmpty();
});

it('rejects an illegal status jump via the API', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Draft]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    Sanctum::actingAs($host);

    $response = $this->patchJson("/api/v1/occasions/{$occasion->slug}", [
        'title' => $occasion->title,
        'type' => $occasion->type->value,
        'status' => OccasionStatus::Completed->value,
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
    expect($occasion->fresh()->status)->toBe(OccasionStatus::Draft);
});

it('lets the host archive a completed occasion via the API', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Completed]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/archive");

    $response->assertOk();
    $response->assertJsonPath('data.status', 'archived');
    expect($occasion->fresh()->status)->toBe(OccasionStatus::Archived);
});

it('rejects archiving a non-completed occasion with a validation envelope', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Active]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/archive");

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('lets the host cancel an occasion before completion via the API', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Planning]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/cancel");

    $response->assertOk();
    $response->assertJsonPath('data.status', 'cancelled');
    expect($occasion->fresh()->status)->toBe(OccasionStatus::Cancelled);
});

it('rejects cancelling a completed occasion with a validation envelope', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Completed]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/cancel");

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('lets the host transfer ownership to an eligible member via the API', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $newHostUser = User::factory()->create();
    $newHostMember = OccasionMember::factory()->role(Role::Chairperson)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $newHostUser->id,
    ]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/transfer-ownership", [
        'member_uuid' => $newHostMember->uuid,
    ]);

    $response->assertOk();
    expect($occasion->fresh()->host_id)->toBe($newHostUser->id)
        ->and($newHostMember->fresh()->role)->toBe(Role::Host);
});

it('rejects transferring ownership to an ineligible member with a validation envelope', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $guestUser = User::factory()->create();
    $guestMember = OccasionMember::factory()->role(Role::Guest)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
    ]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/transfer-ownership", [
        'member_uuid' => $guestMember->uuid,
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
    expect($occasion->fresh()->host_id)->toBe($host->id);
});
