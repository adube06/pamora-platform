<?php

namespace App\Domains\Communication\Domain\Events;

use App\Domains\Communication\Domain\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnnouncementPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Announcement $announcement,
        public readonly User $actor,
    ) {}
}
