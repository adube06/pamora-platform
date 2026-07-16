<?php

namespace App\Domains\Identity\Application\Services;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ResetPasswordService
{
    /**
     * @param  array{token: string, email: string, password: string}  $data
     */
    public function handle(array $data): void
    {
        // Password::reset() does not fire Illuminate\Auth\Events\PasswordReset
        // on its own — dispatching it here (Laravel's documented pattern) is
        // what AuditLogSubscriber listens to instead of a duplicate custom
        // event (see its own docblock).
        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->update(['password' => $password]);

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }
    }
}
