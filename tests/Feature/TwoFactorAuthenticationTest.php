<?php

use PragmaRX\Google2FA\Google2FA;

test('a user can enable and confirm two-factor authentication', function () {
    [, $user] = tenantWithRole('HR Admin');

    $this->actingAs($user)->postJson('/user/confirm-password', ['password' => 'password'])->assertSuccessful();

    $this->actingAs($user)->postJson('/user/two-factor-authentication')->assertSuccessful();

    $user->refresh();
    expect($user->two_factor_secret)->not->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();

    $code = (new Google2FA)->getCurrentOtp(
        decrypt($user->two_factor_secret)
    );

    $this->actingAs($user)
        ->postJson('/user/confirmed-two-factor-authentication', ['code' => $code])
        ->assertSuccessful();

    expect($user->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

test('2FA setup endpoints require a freshly confirmed password', function () {
    [, $user] = tenantWithRole('HR Admin');

    $this->actingAs($user)
        ->postJson('/user/two-factor-authentication')
        ->assertStatus(423);
});

test('a user can disable two-factor authentication once enabled', function () {
    [, $user] = tenantWithRole('HR Admin');

    $this->actingAs($user)->postJson('/user/confirm-password', ['password' => 'password']);
    $this->actingAs($user)->postJson('/user/two-factor-authentication');

    $user->refresh();
    $code = (new Google2FA)->getCurrentOtp(decrypt($user->two_factor_secret));
    $this->actingAs($user)->postJson('/user/confirmed-two-factor-authentication', ['code' => $code]);

    $this->actingAs($user)->deleteJson('/user/two-factor-authentication')->assertSuccessful();

    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();
});
