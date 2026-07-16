<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Checklist;
use App\Models\User;

it('lets an authorized member create a checklist', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/checklists", [
        'name' => 'Catering',
    ]);

    $response->assertSessionHasNoErrors();

    expect(Checklist::firstWhere('name', 'Catering'))->not->toBeNull();
});

it('prevents a member without planning.manage_checklist from creating a checklist', function () {
    $occasion = Occasion::factory()->create();
    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->post("/occasions/{$occasion->slug}/checklists", ['name' => 'Should not be created'])
        ->assertForbidden();

    expect(Checklist::where('name', 'Should not be created')->exists())->toBeFalse();
});

it('rejects creating a checklist on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/checklists", ['name' => 'Should not be created'])
        ->assertSessionHasErrors('occasion');

    expect(Checklist::where('name', 'Should not be created')->exists())->toBeFalse();
});
