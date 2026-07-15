<?php

namespace App\Domains\Communication\Presentation\Policies;

use App\Domains\Communication\Domain\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    public function view(User $user, Notification $notification): bool
    {
        return $notification->user_id === $user->id;
    }

    public function markRead(User $user, Notification $notification): bool
    {
        return $notification->user_id === $user->id;
    }
}
