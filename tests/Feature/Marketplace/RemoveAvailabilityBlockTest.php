<?php

use App\Domains\Marketplace\Domain\Models\AvailabilityBlock;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;

it('lets the owning vendor remove an availability block', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id]);
    $block = AvailabilityBlock::factory()->create(['vendor_id' => $vendor->id]);

    $this->actingAs($owner)
        ->delete("/vendor/availability-blocks/{$block->uuid}")
        ->assertSessionHasNoErrors();

    expect(AvailabilityBlock::find($block->id))->toBeNull();
});

it('prevents a non-owner from removing an availability block', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id]);
    $block = AvailabilityBlock::factory()->create(['vendor_id' => $vendor->id]);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->delete("/vendor/availability-blocks/{$block->uuid}")
        ->assertForbidden();

    expect(AvailabilityBlock::find($block->id))->not->toBeNull();
});
