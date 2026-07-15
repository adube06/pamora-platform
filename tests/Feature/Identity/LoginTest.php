<?php

use App\Models\User;

it('logs in with valid credentials', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('occasions.index'));
    $this->assertAuthenticatedAs($user);
});

it('rejects login with an invalid password', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});
