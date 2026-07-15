<?php

use App\Domains\Finance\Domain\Models\Budget;
use App\Domains\Finance\Domain\Models\Contribution;
use App\Domains\Insights\Application\Services\GetReadinessScoreService;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;

it('returns a null score with no signals when there is no data at all', function () {
    $occasion = Occasion::factory()->create();

    $readiness = app(GetReadinessScoreService::class)->handle($occasion, includeFinance: true);

    expect($readiness['score'])->toBeNull()
        ->and($readiness['signals'])->toBe([]);
});

it('computes a task-only signal when there is no budget', function () {
    $occasion = Occasion::factory()->create();
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Completed]);
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open]);

    $readiness = app(GetReadinessScoreService::class)->handle($occasion, includeFinance: true);

    expect($readiness['score'])->toBe(50)
        ->and($readiness['signals'])->toHaveCount(1)
        ->and($readiness['signals'][0]['key'])->toBe('task_completion')
        ->and($readiness['signals'][0]['value'])->toBe(50);
});

it('excludes cancelled tasks from the task completion signal', function () {
    $occasion = Occasion::factory()->create();
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Completed]);
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Cancelled]);
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Cancelled]);

    $readiness = app(GetReadinessScoreService::class)->handle($occasion, includeFinance: true);

    // 1 completed out of 1 non-cancelled task = 100%, not 33%.
    expect($readiness['signals'][0]['value'])->toBe(100);
});

it('computes a funding-only signal when there are no tasks', function () {
    $occasion = Occasion::factory()->create();
    Budget::factory()->create(['occasion_id' => $occasion->id, 'planned_amount' => 100000]);
    Contribution::factory()->create(['occasion_id' => $occasion->id, 'amount' => 40000]);

    $readiness = app(GetReadinessScoreService::class)->handle($occasion, includeFinance: true);

    expect($readiness['score'])->toBe(40)
        ->and($readiness['signals'])->toHaveCount(1)
        ->and($readiness['signals'][0]['key'])->toBe('funding_progress');
});

it('caps the funding signal at 100 when overfunded', function () {
    $occasion = Occasion::factory()->create();
    Budget::factory()->create(['occasion_id' => $occasion->id, 'planned_amount' => 100000]);
    Contribution::factory()->create(['occasion_id' => $occasion->id, 'amount' => 150000]);

    $readiness = app(GetReadinessScoreService::class)->handle($occasion, includeFinance: true);

    expect($readiness['signals'][0]['value'])->toBe(100);
});

it('averages both signals when both are present', function () {
    $occasion = Occasion::factory()->create();
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Completed]);
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open]);
    Budget::factory()->create(['occasion_id' => $occasion->id, 'planned_amount' => 100000]);
    Contribution::factory()->create(['occasion_id' => $occasion->id, 'amount' => 100000]);

    $readiness = app(GetReadinessScoreService::class)->handle($occasion, includeFinance: true);

    // task_completion 50% + funding_progress 100% averaged = 75%.
    expect($readiness['score'])->toBe(75)
        ->and($readiness['signals'])->toHaveCount(2);
});

it('omits the funding signal when includeFinance is false, even if a budget exists', function () {
    $occasion = Occasion::factory()->create();
    Budget::factory()->create(['occasion_id' => $occasion->id, 'planned_amount' => 100000]);
    Contribution::factory()->create(['occasion_id' => $occasion->id, 'amount' => 40000]);

    $readiness = app(GetReadinessScoreService::class)->handle($occasion, includeFinance: false);

    expect($readiness['score'])->toBeNull()
        ->and($readiness['signals'])->toBe([]);
});

it('shows the funding signal to the host but hides it from a member without finance.view_budget', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    Budget::factory()->create(['occasion_id' => $occasion->id, 'planned_amount' => 100000]);
    Contribution::factory()->create(['occasion_id' => $occasion->id, 'amount' => 40000]);

    $this->actingAs($host)
        ->get("/occasions/{$occasion->slug}")
        ->assertInertia(fn ($page) => $page
            ->component('Occasions/Show')
            ->where('readiness.score', 40)
        );

    $observer = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $observer->id,
        'permissions' => [],
    ]);

    $this->actingAs($observer)
        ->get("/occasions/{$occasion->slug}")
        ->assertInertia(fn ($page) => $page
            ->component('Occasions/Show')
            ->where('readiness.score', null)
            ->where('readiness.signals', [])
        );
});
