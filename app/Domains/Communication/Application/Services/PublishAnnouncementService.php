<?php

namespace App\Domains\Communication\Application\Services;

use App\Domains\Communication\Domain\Events\AnnouncementPublished;
use App\Domains\Communication\Domain\Models\Announcement;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;

class PublishAnnouncementService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * @param  array{title: string, message: string}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Announcement
    {
        $this->ensureOccasionAcceptsActivity($occasion);

        // Refresh after create(): audience/status are left to their database
        // column defaults here — the in-memory model from create() won't
        // reflect them until reloaded (same bug class as Occasion's
        // CreateOccasionService, silent here rather than fatal since these
        // columns aren't enum-cast — audience/status would just read null).
        $announcement = Announcement::create([
            ...$data,
            'occasion_id' => $occasion->id,
            'published_at' => now(),
            'created_by' => $actor->id,
        ])->fresh();

        AnnouncementPublished::dispatch($announcement, $actor);

        return $announcement;
    }
}
