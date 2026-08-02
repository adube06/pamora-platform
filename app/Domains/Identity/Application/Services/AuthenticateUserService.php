<?php

namespace App\Domains\Identity\Application\Services;

use App\Domains\Identity\Domain\Events\UserSignedIn;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticateUserService
{
    /**
     * @param  array{email: string, password: string, remember?: bool}  $credentials
     * @param  bool  $persistSession  Web logins need a real session (Auth::attempt, which
     *                                can set the remember-me cookie and touches the session store). The API/mobile flow
     *                                is stateless bearer-token auth with no session middleware in its route group, so it
     *                                must verify credentials without Auth::attempt's session side effects — Auth::once
     *                                resolves the guard's user for this request only and never writes to the session.
     */
    public function handle(array $credentials, bool $persistSession = true): User
    {
        $attempt = [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ];

        $authenticated = $persistSession
            ? Auth::attempt($attempt, $credentials['remember'] ?? false)
            : Auth::once($attempt);

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        UserSignedIn::dispatch($user);

        return $user;
    }
}
