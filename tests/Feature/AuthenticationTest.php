<?php

test('users can log in with correct credentials', function () {
    [, $user] = tenantWithRole('HR Admin');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/dashboard');
});

test('users cannot log in with incorrect credentials', function () {
    [, $user] = tenantWithRole('HR Admin');

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can log out', function () {
    [, $user] = tenantWithRole('HR Admin');

    $this->actingAs($user)->post('/logout');

    $this->assertGuest();
});

test('login is rate limited after repeated failed attempts', function () {
    [, $user] = tenantWithRole('HR Admin');

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
    }

    $response = $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);

    $response->assertStatus(429);
});
