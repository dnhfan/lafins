<?php

/**
 * @var \Tests\TestCase $this
 * @property \App\Models\User $user
 */

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('user can enable two factor authentication', function () {
    // 👇 URL đúng: /api/settings/2fa/enable
    $response = $this
        ->actingAs($this->user)
        ->postJson('/api/settings/2fa/enable');

    $response->assertStatus(200);

    $response->assertJsonStructure([
        'status',
        'message',
        'data' => [
            'qr_code_svg',
            'setup_key',
            'secret'
        ]
    ]);
});

test('user can confirm two factor authentication with valid code', function () {
    // 1. Enable trước
    $enableResponse = $this
        ->actingAs($this->user)
        ->postJson('/api/settings/2fa/enable');

    $secret = $enableResponse->json('data.secret');

    // 2. Tính OTP
    $google2fa = new Google2FA();
    $validCode = $google2fa->getCurrentOtp($secret);

    // 3. Confirm (URL đúng)
    $response = $this
        ->actingAs($this->user)
        ->postJson('/api/settings/2fa/confirm', [
            'code' => $validCode
        ]);

    $response->assertStatus(200);
    expect($this->user->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

test('user cannot confirm 2fa with invalid code', function () {
    $this->actingAs($this->user)->postJson('/api/settings/2fa/enable');

    $response = $this
        ->actingAs($this->user)
        ->postJson('/api/settings/2fa/confirm', [
            'code' => '123456'
        ]);

    $response->assertStatus(422);
});

test('user can disable two factor authentication', function () {
    // Setup user có secret
    $this->user->forceFill([
        'two_factor_secret' => encrypt('ADUMMYSECRETKEYTHATISLONGENOUGH'),
        'two_factor_confirmed_at' => now(),
    ])->save();

    // 👇 URL đúng: /api/settings/2fa/disable
    $response = $this
        ->actingAs($this->user)
        ->deleteJson('/api/settings/2fa/disable');

    $response->assertStatus(200);

    $this->user->refresh();
    expect($this->user->two_factor_secret)->toBeNull();
});

test('user can check 2fa status', function () {
    // 👇 URL đúng: /api/settings/2fa
    $response = $this->actingAs($this->user)->getJson('/api/settings/2fa');

    $response
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'twoFactorEnabled' => false,
                'pendingConfirmation' => false
            ]
        ]);
});
