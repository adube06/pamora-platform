<?php

namespace App\Domains\Identity\Application\Services;

use App\Domains\Identity\Domain\Events\SessionRevoked;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RevokeSessionService
{
    public function handle(User $user, string $sessionId, User $actor): void
    {
        // Scoped to the owner's own rows — a user can only ever revoke
        // their own sessions, mirroring the Preferences ownership boundary.
        $deleted = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->delete();

        if ($deleted > 0) {
            SessionRevoked::dispatch($user, $sessionId, $actor);
        }
    }
}
