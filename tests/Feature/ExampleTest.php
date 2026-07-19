<?php

test('guests see the landing page', function () {
    $this->get('/')->assertOk()->assertSee('Log in');
});

test('guests are redirected to login from the dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});
