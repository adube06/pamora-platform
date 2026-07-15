<?php

use App\Domains\Communication\Domain\Models\Announcement;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets an authorized member publish an announcement', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/announcements", [
        'title' => 'Venue update',
        'message' => 'The venue has changed to the community hall.',
    ]);

    $response->assertSessionHasNoErrors();

    expect(Announcement::firstWhere('title', 'Venue update'))->not->toBeNull();
});

it('prevents a member without communication.publish_announcement from publishing an announcement', function () {
    $occasion = Occasion::factory()->create();
    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->post("/occasions/{$occasion->slug}/announcements", [
            'title' => 'Should not be published',
            'message' => 'This should not be saved.',
        ])
        ->assertForbidden();

    expect(Announcement::where('title', 'Should not be published')->exists())->toBeFalse();
});
