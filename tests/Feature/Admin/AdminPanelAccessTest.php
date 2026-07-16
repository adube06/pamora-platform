<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Filament\Resources\Occasions\Pages\EditOccasion;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Livewire\Livewire;

it('denies a non-admin user access to the admin panel', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('lets an admin user access the panel dashboard and every registered resource', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin);

    $this->get('/admin')->assertOk();
    $this->get('/admin/users')->assertOk();
    $this->get('/admin/occasions')->assertOk();
    $this->get('/admin/activity-logs')->assertOk();
    $this->get('/admin/reports')->assertOk();
});

it('logs admin.user_updated when an admin edits a user via the panel', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['name' => 'Old Name']);

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm(['name' => 'New Name', 'email' => $user->email])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->name)->toBe('New Name');
    expect(ActivityLog::where('action', 'admin.user_updated')
        ->where('subject_id', $user->id)
        ->exists())->toBeTrue();
});

it('logs admin.occasion_updated when an admin changes an occasions status via the panel', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $occasion = Occasion::factory()->create(['status' => OccasionStatus::Active]);

    $this->actingAs($admin);

    Livewire::test(EditOccasion::class, ['record' => $occasion->getRouteKey()])
        ->fillForm([
            'title' => $occasion->title,
            'type' => $occasion->type->value,
            'visibility' => $occasion->visibility->value,
            'status' => OccasionStatus::Archived->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($occasion->fresh()->status)->toBe(OccasionStatus::Archived);
    expect(ActivityLog::where('action', 'admin.occasion_updated')
        ->where('subject_id', $occasion->id)
        ->exists())->toBeTrue();
});
