<?php

use App\Domains\Insights\Application\Services\GetTaskProgressService;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Models\Task;

it('returns zeroed counts and a null percentage with no tasks', function () {
    $occasion = Occasion::factory()->create();

    $progress = app(GetTaskProgressService::class)->handle($occasion);

    expect($progress['total'])->toBe(0)
        ->and($progress['completion_percentage'])->toBeNull();
});

it('computes counts per status and a completion percentage', function () {
    $occasion = Occasion::factory()->create();
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Completed]);
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Completed]);
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open]);
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::InProgress]);
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Deferred]);

    $progress = app(GetTaskProgressService::class)->handle($occasion);

    expect($progress['total'])->toBe(5)
        ->and($progress['completed'])->toBe(2)
        ->and($progress['open'])->toBe(1)
        ->and($progress['in_progress'])->toBe(1)
        ->and($progress['deferred'])->toBe(1)
        ->and($progress['completion_percentage'])->toBe(40);
});

it('excludes cancelled tasks from the total and percentage', function () {
    $occasion = Occasion::factory()->create();
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Completed]);
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Cancelled]);
    Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Cancelled]);

    $progress = app(GetTaskProgressService::class)->handle($occasion);

    expect($progress['total'])->toBe(1)
        ->and($progress['completion_percentage'])->toBe(100);
});
