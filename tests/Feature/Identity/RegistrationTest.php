<?php

use App\Models\User;

it('registers a new user and logs them in', function () {
    $response = $this->post('/register', [
        'name' => 'Amina Hassan',
        'email' => 'amina@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('occasions.index'));
    $this->assertAuthenticated();

    expect(User::where('email', 'amina@example.com')->exists())->toBeTrue();
});

it('hashes the password, not stores it in plain text', function () {
    $this->post('/register', [
        'name' => 'Amina Hassan',
        'email' => 'amina@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'amina@example.com')->first();

    expect($user->password)->not->toBe('password123');
});

it('rejects registration with a duplicate email', function () {
    User::factory()->create(['email' => 'amina@example.com']);

    $response = $this->post('/register', [
        'name' => 'Amina Hassan',
        'email' => 'amina@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

it('rejects registration when passwords do not match', function () {
    $response = $this->post('/register', [
        'name' => 'Amina Hassan',
        'email' => 'amina@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different',
    ]);

    $response->assertSessionHasErrors('password');
});
