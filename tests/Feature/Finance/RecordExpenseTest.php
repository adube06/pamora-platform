<?php

use App\Domains\Finance\Domain\Models\Budget;
use App\Domains\Finance\Domain\Models\BudgetCategory;
use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets an authorized member record an expense against an existing category', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $budget = Budget::factory()->create(['occasion_id' => $occasion->id]);
    $category = BudgetCategory::factory()->create(['budget_id' => $budget->id, 'name' => 'Venue']);

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/expenses", [
        'budget_category_id' => $category->id,
        'amount' => 300000,
        'spent_at' => now()->toDateString(),
    ]);

    $response->assertSessionHasNoErrors();

    $expense = Expense::firstWhere('budget_category_id', $category->id);
    expect($expense)->not->toBeNull()
        ->and($expense->currency)->toBe('TZS');
});

it('prevents a member without finance.record_expense from recording an expense', function () {
    $occasion = Occasion::factory()->create();
    $budget = Budget::factory()->create(['occasion_id' => $occasion->id]);
    $category = BudgetCategory::factory()->create(['budget_id' => $budget->id]);

    $memberUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $memberUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($memberUser)
        ->post("/occasions/{$occasion->slug}/expenses", [
            'budget_category_id' => $category->id,
            'amount' => 5000,
            'spent_at' => now()->toDateString(),
        ])
        ->assertForbidden();

    expect(Expense::where('budget_category_id', $category->id)->exists())->toBeFalse();
});

it('rejects a zero or negative amount', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $budget = Budget::factory()->create(['occasion_id' => $occasion->id]);
    $category = BudgetCategory::factory()->create(['budget_id' => $budget->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/expenses", [
        'budget_category_id' => $category->id,
        'amount' => 0,
        'spent_at' => now()->toDateString(),
    ])->assertSessionHasErrors('amount');

    expect(Expense::where('budget_category_id', $category->id)->exists())->toBeFalse();
});

it('rejects a category belonging to a different occasion\'s budget', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    Budget::factory()->create(['occasion_id' => $occasion->id]);

    $otherOccasion = Occasion::factory()->create();
    $otherBudget = Budget::factory()->create(['occasion_id' => $otherOccasion->id]);
    $foreignCategory = BudgetCategory::factory()->create(['budget_id' => $otherBudget->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/expenses", [
        'budget_category_id' => $foreignCategory->id,
        'amount' => 5000,
        'spent_at' => now()->toDateString(),
    ])->assertSessionHasErrors('budget_category_id');

    expect(Expense::where('budget_category_id', $foreignCategory->id)->exists())->toBeFalse();
});

it('rejects recording an expense on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $budget = Budget::factory()->create(['occasion_id' => $occasion->id]);
    $category = BudgetCategory::factory()->create(['budget_id' => $budget->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/expenses", [
        'budget_category_id' => $category->id,
        'amount' => 5000,
        'spent_at' => now()->toDateString(),
    ])->assertSessionHasErrors('occasion');

    expect(Expense::where('budget_category_id', $category->id)->exists())->toBeFalse();
});
