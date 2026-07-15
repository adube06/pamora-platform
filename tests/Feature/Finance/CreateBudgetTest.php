<?php

use App\Domains\Finance\Domain\Models\Budget;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets an authorized member create a budget with default categories seeded', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/budget", [
        'name' => 'Wedding Budget',
        'planned_amount' => 2000000,
    ]);

    $response->assertSessionHasNoErrors();

    $budget = Budget::firstWhere('name', 'Wedding Budget');
    expect($budget)->not->toBeNull()
        ->and($budget->currency)->toBe('TZS')
        ->and($budget->categories()->count())->toBe(7);
});

it('lets a treasurer create a budget', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $treasurer = User::factory()->create();
    OccasionMember::factory()->role(Role::Treasurer)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $treasurer->id,
    ]);

    $this->actingAs($treasurer)->post("/occasions/{$occasion->slug}/budget", [
        'name' => 'Treasurer Budget',
        'planned_amount' => 500000,
    ])->assertSessionHasNoErrors();

    expect(Budget::firstWhere('name', 'Treasurer Budget'))->not->toBeNull();
});

it('prevents a member without finance.edit_budget from creating a budget', function () {
    $occasion = Occasion::factory()->create();
    $memberUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $memberUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($memberUser)
        ->post("/occasions/{$occasion->slug}/budget", [
            'name' => 'Should not be created',
            'planned_amount' => 100000,
        ])
        ->assertForbidden();

    expect(Budget::where('name', 'Should not be created')->exists())->toBeFalse();
});

it('rejects a second budget for an occasion that already has one', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    Budget::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/budget", [
        'name' => 'Second Budget',
        'planned_amount' => 100000,
    ])->assertSessionHasErrors('name');

    expect(Budget::where('occasion_id', $occasion->id)->count())->toBe(1);
});
