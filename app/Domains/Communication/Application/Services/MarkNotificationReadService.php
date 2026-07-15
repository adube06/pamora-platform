<?php

namespace App\Domains\Communication\Application\Services;

use App\Domains\Communication\Domain\Models\Notification;

class MarkNotificationReadService
{
    public function handle(Notification $notification): Notification
    {
        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return $notification;
    }
}
