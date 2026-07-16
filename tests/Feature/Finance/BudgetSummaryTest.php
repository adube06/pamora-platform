<?php

use App\Domains\Finance\Application\Services\GetBudgetSummaryService;
use App\Domains\Finance\Domain\Enums\PledgeStatus;
use App\Domains\Finance\Domain\Models\Budget;
use App\Domains\Finance\Domain\Models\BudgetCategory;
use App\Domains\Finance\Domain\Models\Contribution;
use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Finance\Domain\Models\Pledge;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('computes derived totals fresh from source rows', function () {
    $occasion = Occasion::factory()->create();
    $budget = Budget::factory()->create(['occasion_id' => $occasion->id, 'planned_amount' => 100000]);
    $category = BudgetCategory::factory()->create(['budget_id' => $budget->id]);

    Contribution::factory()->create(['occasion_id' => $occasion->id, 'amount' => 40000]);
    Expense::factory()->create(['occasion_id' => $occasion->id, 'budget_category_id' => $category->id, 'amount' => 30000]);

    $summary = app(GetBudgetSummaryService::class)->handle($occasion);

    expect($summary['planned_amount'])->toBe('100000.00')
        ->and($summary['total_received'])->toBe((string) Contribution::where('occasion_id', $occasion->id)->sum('amount'))
        ->and($summary['total_expense'])->toBe((string) Expense::where('occasion_id', $occasion->id)->sum('amount'))
        ->and((float) $summary['remaining_budget'])->toBe(70000.0)
        ->and($summary['funding_progress'])->toBe(40.0)
        ->and($summary['spending_progress'])->toBe(30.0);
});

it('counts confirmed pledges toward funding progress but not pending ones', function () {
    $occasion = Occasion::factory()->create();
    Budget::factory()->create(['occasion_id' => $occasion->id, 'planned_amount' => 100000]);

    Contribution::factory()->create(['occasion_id' => $occasion->id, 'amount' => 20000]);
    Pledge::factory()->create(['occasion_id' => $occasion->id, 'amount' => 30000, 'status' => PledgeStatus::Confirmed]);
    Pledge::factory()->create(['occasion_id' => $occasion->id, 'amount' => 15000, 'status' => PledgeStatus::Pending]);

    $summary = app(GetBudgetSummaryService::class)->handle($occasion);

    expect((float) $summary['total_pledged'])->toBe(30000.0)
        ->and((float) $summary['pending_pledged'])->toBe(15000.0)
        // funding_progress = (received 20000 + confirmed pledged 30000) / 100000 * 100
        ->and($summary['funding_progress'])->toBe(50.0);
});

it('returns a null-safe summary when no budget exists yet', function () {
    $occasion = Occasion::factory()->create();
    Contribution::factory()->create(['occasion_id' => $occasion->id, 'amount' => 10000]);

    $summary = app(GetBudgetSummaryService::class)->handle($occasion);

    expect($summary['planned_amount'])->toBeNull()
        ->and($summary['total_received'])->toBe((string) Contribution::where('occasion_id', $occasion->id)->sum('amount'))
        ->and($summary['remaining_budget'])->toBeNull()
        ->and($summary['health'])->toBeNull();
});

it('classifies budget health at each threshold', function () {
    $occasion = Occasion::factory()->create();
    $budget = Budget::factory()->create(['occasion_id' => $occasion->id, 'planned_amount' => 100000]);
    $category = BudgetCategory::factory()->create(['budget_id' => $budget->id]);

    Expense::factory()->create(['occasion_id' => $occasion->id, 'budget_category_id' => $category->id, 'amount' => 30000]);
    expect(app(GetBudgetSummaryService::class)->handle($occasion)['health'])->toBe('under_budget');

    Expense::factory()->create(['occasion_id' => $occasion->id, 'budget_category_id' => $category->id, 'amount' => 40000]);
    expect(app(GetBudgetSummaryService::class)->handle($occasion)['health'])->toBe('on_track');

    Expense::factory()->create(['occasion_id' => $occasion->id, 'budget_category_id' => $category->id, 'amount' => 25000]);
    expect(app(GetBudgetSummaryService::class)->handle($occasion)['health'])->toBe('at_risk');

    Expense::factory()->create(['occasion_id' => $occasion->id, 'budget_category_id' => $category->id, 'amount' => 20000]);
    expect(app(GetBudgetSummaryService::class)->handle($occasion)['health'])->toBe('over_budget');
});

it('hides budget figures on the finance page from a member without finance.view_budget', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    Budget::factory()->create(['occasion_id' => $occasion->id, 'planned_amount' => 100000]);

    $observer = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $observer->id,
        'permissions' => [],
    ]);

    $response = $this->actingAs($observer)->get("/occasions/{$occasion->slug}/finance");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Occasions/Finance')
        ->where('canViewBudget', false)
        ->where('budget', null)
        ->missing('summary.planned_amount')
    );
});

it('shows budget figures on the finance page to the host', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    Budget::factory()->create(['occasion_id' => $occasion->id, 'planned_amount' => 100000]);

    $response = $this->actingAs($host)->get("/occasions/{$occasion->slug}/finance");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Occasions/Finance')
        ->where('canViewBudget', true)
        ->where('summary.planned_amount', '100000.00')
    );
});
