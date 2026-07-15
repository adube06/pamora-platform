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
     */
    public function handle(array $credentials): User
    {
        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $credentials['remember'] ?? false)) {
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
