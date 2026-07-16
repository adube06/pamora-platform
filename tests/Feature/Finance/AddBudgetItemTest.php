<?php

use App\Domains\Finance\Domain\Models\Budget;
use App\Domains\Finance\Domain\Models\BudgetCategory;
use App\Domains\Finance\Domain\Models\BudgetItem;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets an authorized member add a budget item to an existing category', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $budget = Budget::factory()->create(['occasion_id' => $occasion->id]);
    $category = BudgetCategory::factory()->create(['budget_id' => $budget->id, 'name' => 'Decoration']);

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/budget-items", [
        'budget_category_id' => $category->id,
        'name' => 'Balloon arch',
        'estimated_cost' => 800000,
    ]);

    $response->assertSessionHasNoErrors();

    $item = BudgetItem::firstWhere('name', 'Balloon arch');
    expect($item)->not->toBeNull()
        ->and($item->budget_category_id)->toBe($category->id)
        ->and($item->currency)->toBe('TZS');
});

it('rejects a category belonging to a different occasion\'s budget', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    Budget::factory()->create(['occasion_id' => $occasion->id]);

    $otherOccasion = Occasion::factory()->create();
    $otherBudget = Budget::factory()->create(['occasion_id' => $otherOccasion->id]);
    $foreignCategory = BudgetCategory::factory()->create(['budget_id' => $otherBudget->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/budget-items", [
        'budget_category_id' => $foreignCategory->id,
        'name' => 'Should not save',
        'estimated_cost' => 5000,
    ])->assertSessionHasErrors('budget_category_id');

    expect(BudgetItem::where('budget_category_id', $foreignCategory->id)->exists())->toBeFalse();
});

it('prevents a member without finance.edit_budget from adding a budget item', function () {
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
        ->post("/occasions/{$occasion->slug}/budget-items", [
            'budget_category_id' => $category->id,
            'name' => 'Should not save',
            'estimated_cost' => 5000,
        ])
        ->assertForbidden();

    expect(BudgetItem::where('budget_category_id', $category->id)->exists())->toBeFalse();
});
