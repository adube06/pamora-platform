<?php

use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;

it('lets an authenticated user apply to become a vendor', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/vendor', [
            'business_name' => 'Amina Photography',
            'categories' => ['photography', 'dj'],
            'service_areas' => ['Dar es Salaam', 'Arusha'],
            'contact_email' => 'hello@aminaphotography.example',
            'contact_phone' => '+255700000000',
        ])
        ->assertSessionHasNoErrors();

    $vendor = Vendor::firstWhere('owner_id', $user->id);

    expect($vendor)->not->toBeNull()
        ->and($vendor->business_name)->toBe('Amina Photography')
        ->and($vendor->categories)->toBe(['photography', 'dj'])
        ->and($vendor->service_areas)->toBe(['Dar es Salaam', 'Arusha'])
        ->and($vendor->verification_status)->toBe(VendorVerificationStatus::Pending);
});

it('rejects a second application from the same user', function () {
    $user = User::factory()->create();
    Vendor::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->post('/vendor', [
            'business_name' => 'Second Business',
            'categories' => ['catering'],
            'contact_email' => 'second@example.com',
            'contact_phone' => '+255700000001',
        ])
        ->assertSessionHasErrors('business_name');

    expect(Vendor::where('owner_id', $user->id)->count())->toBe(1);
});

it('rejects an invalid category', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/vendor', [
            'business_name' => 'Amina Photography',
            'categories' => ['not-a-real-category'],
            'contact_email' => 'hello@aminaphotography.example',
            'contact_phone' => '+255700000000',
        ])
        ->assertSessionHasErrors('categories.0');

    expect(Vendor::where('owner_id', $user->id)->exists())->toBeFalse();
});

it('shows the applicant their vendor profile once applied', function () {
    $user = User::factory()->create();
    Vendor::factory()->create(['owner_id' => $user->id, 'business_name' => 'Amina Photography']);

    $this->actingAs($user)
        ->get('/vendor')
        ->assertInertia(fn ($page) => $page
            ->component('Marketplace/Profile')
            ->where('vendor.business_name', 'Amina Photography')
        );
});

it('shows the apply form when the user has no vendor profile yet', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/vendor')
        ->assertInertia(fn ($page) => $page->component('Marketplace/Apply'));
});
