<?php

namespace App\Domains\Identity\Application\Services;

use App\Domains\Identity\Domain\Events\UserSignedOut;
use App\Models\User;

class LogoutUserService
{
    /**
     * Revokes the bearer token the mobile/API caller authenticated with.
     */
    public function handleApiLogout(User $user): void
    {
        $user->currentAccessToken()->delete();

        UserSignedOut::dispatch($user);
    }

    /**
     * The web guard has no access token to revoke — session invalidation is
     * handled by the caller, which needs the authenticated $request to do it.
     */
    public function handleWebLogout(User $user): void
    {
        UserSignedOut::dispatch($user);
    }
}
