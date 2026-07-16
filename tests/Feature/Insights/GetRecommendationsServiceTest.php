<?php

use App\Domains\Finance\Domain\Models\Budget;
use App\Domains\Finance\Domain\Models\BudgetCategory;
use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Insights\Application\Services\GetRecommendationsService;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\InvitationStatus;
use App\Domains\People\Domain\Models\Invitation;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Models\Task;

it('returns an empty list for a healthy occasion', function () {
    $occasion = Occasion::factory()->create();

    $recommendations = app(GetRecommendationsService::class)->handle($occasion, includeFinance: true);

    expect($recommendations)->toBe([]);
});

it('recommends attention for overdue tasks, escalating to critical at three or more', function () {
    $occasion = Occasion::factory()->create();
    Task::factory()->create(['occasion_id' => $occasion->id, 'due_date' => now()->subDay(), 'status' => TaskStatus::Open]);

    $recommendations = app(GetRecommendationsService::class)->handle($occasion, includeFinance: true);

    expect($recommendations)->toHaveCount(1)
        ->and($recommendations[0]['message'])->toBe('1 task overdue.')
        ->and($recommendations[0]['severity'])->toBe('warning')
        ->and($recommendations[0]['reason'])->not->toBeEmpty();

    Task::factory()->count(2)->create(['occasion_id' => $occasion->id, 'due_date' => now()->subDay(), 'status' => TaskStatus::Open]);

    $recommendations = app(GetRecommendationsService::class)->handle($occasion, includeFinance: true);

    expect($recommendations[0]['message'])->toBe('3 tasks overdue.')
        ->and($recommendations[0]['severity'])->toBe('critical');
});

it('does not count completed or cancelled tasks as overdue', function () {
    $occasion = Occasion::factory()->create();
    Task::factory()->create(['occasion_id' => $occasion->id, 'due_date' => now()->subDay(), 'status' => TaskStatus::Completed]);
    Task::factory()->create(['occasion_id' => $occasion->id, 'due_date' => now()->subDay(), 'status' => TaskStatus::Cancelled]);

    $recommendations = app(GetRecommendationsService::class)->handle($occasion, includeFinance: true);

    expect($recommendations)->toBe([]);
});

it('recommends attention when spending exceeds the planned budget, only when finance is visible', function () {
    $occasion = Occasion::factory()->create();
    $budget = Budget::factory()->create(['occasion_id' => $occasion->id, 'planned_amount' => 100000]);
    $category = BudgetCategory::factory()->create(['budget_id' => $budget->id]);
    Expense::factory()->create(['occasion_id' => $occasion->id, 'budget_category_id' => $category->id, 'amount' => 150000]);

    $recommendations = app(GetRecommendationsService::class)->handle($occasion, includeFinance: true);

    expect(collect($recommendations)->pluck('message'))->toContain('Budget exceeds the planned amount.');

    $recommendationsHidden = app(GetRecommendationsService::class)->handle($occasion, includeFinance: false);

    expect(collect($recommendationsHidden)->pluck('message'))->not->toContain('Budget exceeds the planned amount.');
});

it('recommends attention for a low invitation acceptance rate once enough invitations have been sent', function () {
    $occasion = Occasion::factory()->create();
    Invitation::factory()->create(['occasion_id' => $occasion->id, 'status' => InvitationStatus::Accepted]);
    Invitation::factory()->create(['occasion_id' => $occasion->id, 'status' => InvitationStatus::Pending]);
    Invitation::factory()->create(['occasion_id' => $occasion->id, 'status' => InvitationStatus::Pending]);
    Invitation::factory()->create(['occasion_id' => $occasion->id, 'status' => InvitationStatus::Declined]);

    $recommendations = app(GetRecommendationsService::class)->handle($occasion, includeFinance: true);

    expect(collect($recommendations)->pluck('message'))->toContain('Invitation response rate is below target.');
});

it('does not recommend on a low acceptance rate when too few invitations have been sent', function () {
    $occasion = Occasion::factory()->create();
    Invitation::factory()->create(['occasion_id' => $occasion->id, 'status' => InvitationStatus::Pending]);

    $recommendations = app(GetRecommendationsService::class)->handle($occasion, includeFinance: true);

    expect(collect($recommendations)->pluck('message'))->not->toContain('Invitation response rate is below target.');
});
