<?php

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;

it('creates an occasion and automatically assigns the host as an active OccasionMember', function () {
    $host = User::factory()->create();

    $response = $this->actingAs($host)->post('/occasions', [
        'title' => "Amina & John's Wedding",
        'type' => 'wedding',
        'primary_date' => now()->addMonth()->toDateString(),
    ]);

    $occasion = Occasion::firstWhere('title', "Amina & John's Wedding");

    expect($occasion)->not->toBeNull()
        ->and($occasion->host_id)->toBe($host->id);

    $response->assertRedirect(route('occasions.show', $occasion->slug));

    $member = $occasion->memberFor($host);

    expect($member)->not->toBeNull()
        ->and($member->status->value)->toBe('active')
        ->and($member->hasPermission(Permission::OccasionEdit))->toBeTrue();
});

it('generates a unique slug when titles collide', function () {
    $host = User::factory()->create();

    $this->actingAs($host)->post('/occasions', ['title' => 'Birthday Party', 'type' => 'birthday']);
    $this->actingAs($host)->post('/occasions', ['title' => 'Birthday Party', 'type' => 'birthday']);

    $slugs = Occasion::where('title', 'Birthday Party')->pluck('slug');

    expect($slugs)->toHaveCount(2)
        ->and($slugs->unique())->toHaveCount(2);
});

it('prevents a non-member from viewing an occasion', function () {
    $occasion = Occasion::factory()->create();
    OccasionMember::factory()->host()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $occasion->host_id,
    ]);

    $stranger = User::factory()->create();

    $this->actingAs($stranger)
        ->get(route('occasions.show', $occasion->slug))
        ->assertForbidden();
});

it('requires authentication to create an occasion', function () {
    $this->post('/occasions', ['title' => 'Test', 'type' => 'wedding'])
        ->assertRedirect('/login');
});
