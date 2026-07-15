<?php

use App\Domains\Finance\Application\Services\GetContributionSummaryService;
use App\Domains\Finance\Domain\Models\Contribution;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('computes the total and count fresh from the contributions rows', function () {
    $occasion = Occasion::factory()->create();

    Contribution::factory()->create(['occasion_id' => $occasion->id, 'amount' => 10000]);
    Contribution::factory()->create(['occasion_id' => $occasion->id, 'amount' => 25000]);
    Contribution::factory()->create(['occasion_id' => $occasion->id, 'amount' => 5000]);

    // A contribution to a different Occasion must not leak into this total.
    Contribution::factory()->create(['amount' => 999999]);

    $summary = app(GetContributionSummaryService::class)->handle($occasion);

    expect($summary['total_received'])->toBe((string) Contribution::where('occasion_id', $occasion->id)->sum('amount'))
        ->and((float) $summary['total_received'])->toBe(40000.0)
        ->and($summary['contribution_count'])->toBe(3);
});

it('returns a zero summary when an occasion has no contributions', function () {
    $occasion = Occasion::factory()->create();

    $summary = app(GetContributionSummaryService::class)->handle($occasion);

    expect($summary['total_received'])->toBe('0')
        ->and($summary['contribution_count'])->toBe(0);
});

it('shows the summary on the finance page for any active member', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    Contribution::factory()->create(['occasion_id' => $occasion->id, 'amount' => 15000]);

    $response = $this->actingAs($host)->get("/occasions/{$occasion->slug}/finance");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Occasions/Finance')
        ->where('summary.contribution_count', 1)
    );
});
