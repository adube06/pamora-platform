<?php

namespace App\Domains\Identity\Application\Services;

use App\Domains\Identity\Domain\Events\UserRegistered;
use App\Models\User;

class RegisterUserService
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function handle(array $data): User
    {
        // The User model casts 'password' as 'hashed', so the raw value is
        // hashed automatically on save — do not hash it again here.
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        UserRegistered::dispatch($user);

        return $user;
    }
}
