<?php

use App\Domains\Finance\Domain\Models\Contribution;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets an authorized member record a contribution', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/contributions", [
        'contributor_name' => 'Amina Hassan',
        'amount' => 50000,
        'method' => 'cash',
        'contributed_at' => now()->toDateString(),
    ]);

    $response->assertSessionHasNoErrors();

    expect(Contribution::firstWhere('contributor_name', 'Amina Hassan'))->not->toBeNull();
});

it('lets a treasurer record a contribution', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $treasurer = User::factory()->create();
    OccasionMember::factory()->role(Role::Treasurer)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $treasurer->id,
    ]);

    $this->actingAs($treasurer)->post("/occasions/{$occasion->slug}/contributions", [
        'contributor_name' => 'Juma Mwita',
        'amount' => 25000,
        'method' => 'mobile_money',
        'contributed_at' => now()->toDateString(),
    ])->assertSessionHasNoErrors();

    expect(Contribution::firstWhere('contributor_name', 'Juma Mwita'))->not->toBeNull();
});

it('prevents a member without finance.record_contribution from recording a contribution', function () {
    $occasion = Occasion::factory()->create();
    $memberUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $memberUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($memberUser)
        ->post("/occasions/{$occasion->slug}/contributions", [
            'contributor_name' => 'Should not be created',
            'amount' => 1000,
            'method' => 'cash',
            'contributed_at' => now()->toDateString(),
        ])
        ->assertForbidden();

    expect(Contribution::where('contributor_name', 'Should not be created')->exists())->toBeFalse();
});

it('rejects a contribution with a zero or negative amount', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/contributions", [
        'contributor_name' => 'Amina Hassan',
        'amount' => 0,
        'method' => 'cash',
        'contributed_at' => now()->toDateString(),
    ])->assertSessionHasErrors('amount');

    expect(Contribution::where('contributor_name', 'Amina Hassan')->exists())->toBeFalse();
});
