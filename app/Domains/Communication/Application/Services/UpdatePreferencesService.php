<?php

namespace App\Domains\Communication\Application\Services;

use App\Domains\Communication\Domain\Events\PreferenceUpdated;
use App\Models\User;

class UpdatePreferencesService
{
    /**
     * @param  array<string, mixed>  $preferences
     */
    public function handle(User $user, array $preferences): User
    {
        // Cast explicitly rather than trusting the transport representation —
        // an unchecked checkbox/false value can arrive as an empty string or
        // null depending on how the client encodes it, and null would
        // otherwise be indistinguishable from "not provided" once merged.
        $preferences = array_map(fn (mixed $value): bool => (bool) $value, $preferences);

        $user->update([
            'notification_preferences' => [...($user->notification_preferences ?? []), ...$preferences],
        ]);

        PreferenceUpdated::dispatch($user, $user);

        return $user;
    }
}
