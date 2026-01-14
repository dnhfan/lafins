<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// 1. Test Đăng ký thành công
test('user can register with valid data', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Arch User',
        'email' => 'arch@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response
        ->assertStatus(201)
        ->assertJson(['status' => 'success'])
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
            ],
        ]);

    $this->assertDatabaseHas('users', ['email' => 'arch@example.com']);
});

// 2. ✅ ĐÃ FIX: Test Validation lỗi (Soi vào key 'error')
test('registration fails with validation errors', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'User',
        'email' => 'invalid-email',
        'password' => '123',
        'password_confirmation' => '456',  // Pass không khớp
    ]);

    $response
        ->assertStatus(422)
        ->assertJson(['status' => 'error'])
        // Thay vì dùng assertJsonValidationErrors, ta soi thẳng vào key 'error'
        ->assertJsonStructure([
            'error' => ['email', 'password']
        ]);
});

// 3. Test Đăng nhập thành công
test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'login@example.com',
        'password' => 'Password123!',
    ]);

    $response
        ->assertStatus(200)
        ->assertJson(['status' => 'success'])
        ->assertJsonStructure([
            'data' => [
                'token',
                'user',
            ],
        ]);
});

// 4. ✅ ĐÃ FIX: Test Đăng nhập thất bại (Chấp nhận 422)
test('login fails with invalid credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('CorrectPassword'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'WrongPassword',
    ]);

    // Laravel trả về 422 khi sai pass (Validation Exception)
    $response
        ->assertStatus(422)
        ->assertJson(['status' => 'error']);
});

// 5. ✅ ĐÃ FIX: Test Profile (Thêm key 'user' bọc ngoài)
test('authenticated user can get their profile', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson('/api/user');

    $response
        ->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'data' => [
                'user' => [  // Bọc thêm user để khớp với response
                    'id' => $user->id,
                    'email' => $user->email,
                ]
            ],
        ]);
});

// 6. Test Logout
test('authenticated user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->postJson('/api/logout');

    $response
        ->assertStatus(200)
        ->assertJson(['status' => 'success']);

    expect($user->tokens()->count())->toBe(0);
});

// 7. Test Guest
test('guest cannot access protected routes', function () {
    $this->getJson('/api/user')->assertStatus(401);
});
