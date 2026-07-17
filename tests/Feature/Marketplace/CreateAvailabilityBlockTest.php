<?php

use App\Domains\Marketplace\Domain\Models\AvailabilityBlock;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;

it('lets the owning vendor create an availability block', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($owner)
        ->post("/vendor/{$vendor->uuid}/availability-blocks", [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-03',
            'reason' => 'Public holiday',
        ])
        ->assertSessionHasNoErrors();

    $block = AvailabilityBlock::where('vendor_id', $vendor->id)->first();

    expect($block)->not->toBeNull()
        ->and($block->start_date->toDateString())->toBe('2026-08-01')
        ->and($block->end_date->toDateString())->toBe('2026-08-03')
        ->and($block->reason)->toBe('Public holiday');
});

it('rejects an end date before the start date', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($owner)
        ->post("/vendor/{$vendor->uuid}/availability-blocks", [
            'start_date' => '2026-08-03',
            'end_date' => '2026-08-01',
        ])
        ->assertSessionHasErrors('end_date');

    expect(AvailabilityBlock::where('vendor_id', $vendor->id)->exists())->toBeFalse();
});

it('prevents a non-owner from creating an availability block for another vendor', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id]);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->post("/vendor/{$vendor->uuid}/availability-blocks", [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-03',
        ])
        ->assertForbidden();

    expect(AvailabilityBlock::where('vendor_id', $vendor->id)->exists())->toBeFalse();
});
