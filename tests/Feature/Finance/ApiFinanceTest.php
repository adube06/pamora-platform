<?php

use App\Domains\Finance\Domain\Models\Budget;
use App\Domains\Finance\Domain\Models\BudgetCategory;
use App\Domains\Finance\Domain\Models\Contribution;
use App\Domains\Finance\Domain\Models\Pledge;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function occasionWithHostFinance(): array
{
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'created_by' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    return [$occasion, $host];
}

// --- Dashboard ---

it('returns the full finance dashboard for a member with view-budget', function () {
    [$occasion, $host] = occasionWithHostFinance();
    Budget::factory()->create(['occasion_id' => $occasion->id]);
    Contribution::factory()->create(['occasion_id' => $occasion->id]);

    Sanctum::actingAs($host);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/finance");

    $response->assertOk();
    $response->assertJsonPath('success', true);
    expect($response->json('data.budget'))->not->toBeNull();
    $response->assertJsonPath('data.permissions.can_view_budget', true);
    $response->assertJsonCount(1, 'data.contributions');
});

it('withholds budget and expense figures from a member without finance.view_budget', function () {
    [$occasion, $host] = occasionWithHostFinance();
    Budget::factory()->create(['occasion_id' => $occasion->id]);
    $member = User::factory()->create();
    OccasionMember::factory()->role(Role::Member)->create(['occasion_id' => $occasion->id, 'user_id' => $member->id]);

    Sanctum::actingAs($member);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/finance");

    $response->assertOk();
    $response->assertJsonPath('data.budget', null);
    $response->assertJsonPath('data.expenses', []);
    $response->assertJsonPath('data.permissions.can_view_budget', false);
    expect($response->json('data.summary'))->toHaveKeys(['total_received', 'contribution_count', 'total_pledged', 'pending_pledged'])
        ->not->toHaveKey('total_expense');
});

it('rejects the finance dashboard for a non-member', function () {
    [$occasion] = occasionWithHostFinance();
    Sanctum::actingAs(User::factory()->create());

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/finance");

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

// --- Pledges (the critical fix) ---

it('lets an occasion member list pledges via the API', function () {
    [$occasion, $host] = occasionWithHostFinance();
    Pledge::factory()->create(['occasion_id' => $occasion->id]);

    Sanctum::actingAs($host);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/pledges");

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('rejects a non-member from listing pledges via the API', function () {
    [$occasion] = occasionWithHostFinance();
    Pledge::factory()->create(['occasion_id' => $occasion->id, 'pledgor_name' => 'Secret Pledgor']);

    Sanctum::actingAs(User::factory()->create());

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/pledges");

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

it('records a pledge via the API and logs an audit entry', function () {
    [$occasion, $host] = occasionWithHostFinance();
    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/pledges", [
        'pledgor_name' => 'Amina',
        'amount' => 50000,
        'pledged_at' => now()->toDateString(),
    ]);

    $response->assertCreated();
    $pledge = Pledge::firstWhere('pledgor_name', 'Amina');

    expect(ActivityLog::where('action', 'finance.pledge_recorded')
        ->where('subject_id', $pledge->id)
        ->count())->toBe(1);
});

it('updates a pledge status via the API', function () {
    [$occasion, $host] = occasionWithHostFinance();
    $pledge = Pledge::factory()->create(['occasion_id' => $occasion->id]);

    Sanctum::actingAs($host);

    $response = $this->patchJson("/api/v1/occasions/{$occasion->slug}/pledges/{$pledge->uuid}", ['status' => 'confirmed']);

    $response->assertOk();
    $response->assertJsonPath('data.status', 'confirmed');
});

// --- Budget & Budget Items ---

it('creates a budget via the API with default categories', function () {
    [$occasion, $host] = occasionWithHostFinance();
    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/budget", [
        'name' => 'Wedding Budget',
        'planned_amount' => 2000000,
    ]);

    $response->assertCreated();
    $response->assertJsonCount(7, 'data.categories');
});

it('rejects creating a second budget for the same occasion', function () {
    [$occasion, $host] = occasionWithHostFinance();
    Budget::factory()->create(['occasion_id' => $occasion->id]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/budget", [
        'name' => 'Second Budget',
        'planned_amount' => 100000,
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('returns budget items nested under categories when reading the budget via the API', function () {
    [$occasion, $host] = occasionWithHostFinance();
    $budget = Budget::factory()->create(['occasion_id' => $occasion->id]);
    $category = BudgetCategory::factory()->create(['budget_id' => $budget->id, 'name' => 'Catering']);

    Sanctum::actingAs($host);

    $this->postJson("/api/v1/occasions/{$occasion->slug}/budget-items", [
        'budget_category_id' => $category->id,
        'name' => 'Buffet dinner',
        'estimated_cost' => 500000,
    ])->assertCreated();

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/budget");

    $response->assertOk();
    $categoryData = collect($response->json('data.categories'))->firstWhere('name', 'Catering');
    expect($categoryData['budget_items'])->toHaveCount(1)
        ->and($categoryData['budget_items'][0]['name'])->toBe('Buffet dinner');
});

it('rejects viewing the budget for a member without finance.view_budget', function () {
    [$occasion, $host] = occasionWithHostFinance();
    Budget::factory()->create(['occasion_id' => $occasion->id]);
    $member = User::factory()->create();
    OccasionMember::factory()->role(Role::Member)->create(['occasion_id' => $occasion->id, 'user_id' => $member->id]);

    Sanctum::actingAs($member);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/budget");

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

// --- Contributions & Expenses ---

it('lets any occasion member list contributions with a summary via the API', function () {
    [$occasion, $host] = occasionWithHostFinance();
    Contribution::factory()->create(['occasion_id' => $occasion->id, 'amount' => 10000]);
    $member = User::factory()->create();
    OccasionMember::factory()->role(Role::Member)->create(['occasion_id' => $occasion->id, 'user_id' => $member->id]);

    Sanctum::actingAs($member);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/contributions");

    $response->assertOk();
    $response->assertJsonPath('meta.summary.total_received', '10000');
});

it('records an expense via the API', function () {
    [$occasion, $host] = occasionWithHostFinance();
    $budget = Budget::factory()->create(['occasion_id' => $occasion->id]);
    $category = BudgetCategory::factory()->create(['budget_id' => $budget->id]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/expenses", [
        'budget_category_id' => $category->id,
        'amount' => 75000,
        'spent_at' => now()->toDateString(),
    ]);

    $response->assertCreated();
});

it('rejects listing expenses for a member without finance.view_budget', function () {
    [$occasion, $host] = occasionWithHostFinance();
    $member = User::factory()->create();
    OccasionMember::factory()->role(Role::Member)->create(['occasion_id' => $occasion->id, 'user_id' => $member->id]);

    Sanctum::actingAs($member);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/expenses");

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});
