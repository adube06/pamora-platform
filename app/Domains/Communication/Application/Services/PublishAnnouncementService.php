<?php

namespace App\Domains\Communication\Application\Services;

use App\Domains\Communication\Domain\Events\AnnouncementPublished;
use App\Domains\Communication\Domain\Models\Announcement;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;

class PublishAnnouncementService
{
    /**
     * @param  array{title: string, message: string}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Announcement
    {
        $announcement = Announcement::create([
            ...$data,
            'occasion_id' => $occasion->id,
            'published_at' => now(),
            'created_by' => $actor->id,
        ]);

        AnnouncementPublished::dispatch($announcement, $actor);

        return $announcement;
    }
}
