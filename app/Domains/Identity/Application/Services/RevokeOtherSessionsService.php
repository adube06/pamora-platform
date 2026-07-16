<?php

namespace App\Domains\Identity\Application\Services;

use App\Domains\Identity\Domain\Events\SessionRevoked;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RevokeOtherSessionsService
{
    public function handle(User $user, string $currentSessionId, User $actor): int
    {
        $otherSessionIds = DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->pluck('id');

        if ($otherSessionIds->isEmpty()) {
            return 0;
        }

        DB::table('sessions')
            ->whereIn('id', $otherSessionIds)
            ->delete();

        foreach ($otherSessionIds as $sessionId) {
            SessionRevoked::dispatch($user, $sessionId, $actor);
        }

        return $otherSessionIds->count();
    }
}
