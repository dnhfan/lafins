<?php

/**
 * @var \Tests\TestCase $this
 * @property \App\Models\User $user
 * @property \App\Models\Jar $jarNec
 */

use App\Models\Outcome;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // 1. Tạo user (Observer tự tạo 6 hũ)
    $this->user = User::factory()->create();

    // 2. Xóa dữ liệu rác (nếu có từ Seeder)
    Outcome::query()->delete();

    // 3. Lấy hũ NEC ra và BƠM TIỀN vào để có cái mà tiêu
    $this->jarNec = $this->user->jars()->where('name', 'NEC')->first();
    $this->jarNec->update(['balance' => 5000000]);  // Có 5 triệu
    $this->jarFfa = $this->user->jars()->where('name', 'FFA')->first();
});

// Case 1: Liệt kê chi tiêu
test('authenticated user can list their outcomes', function () {
    $testDate = '2025-01-01';

    Outcome::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'jar_id' => $this->jarNec->id,
        'date' => $testDate
    ]);

    // THÊM &range=custom VÀO ĐÂY
    $response = $this
        ->actingAs($this->user)
        ->getJson("/api/outcomes?range=custom&start={$testDate}&end={$testDate}");

    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data.outcomes.data');
});

// Case 2: Thêm chi tiêu -> Tiền hũ phải GIẢM
test('adding outcome decreases jar balance', function () {
    $initialBalance = $this->jarNec->fresh()->balance;  // 5,000,000
    $spendAmount = 200000;  // Tiêu 200k

    $response = $this
        ->actingAs($this->user)
        ->postJson('/api/outcomes', [
            'jar_id' => $this->jarNec->id,
            'amount' => $spendAmount,
            'category' => 'Shopping',
            'description' => 'Mua đồ Shopee',
            'date' => now()->format('Y-m-d'),
        ]);

    $response->assertStatus(201);

    // Kiểm tra: 5tr - 200k = 4tr8
    expect((int) $this->jarNec->fresh()->balance)
        ->toEqual((int) ($initialBalance - $spendAmount));
});

// Case 3: Không được tiêu số tiền âm
test('cannot add outcome with negative amount', function () {
    $response = $this
        ->actingAs($this->user)
        ->postJson('/api/outcomes', [
            'jar_id' => $this->jarNec->id,
            'amount' => -50000,
            'date' => now()->format('Y-m-d'),
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['amount']);
});

// Case 4: Không được xem chi tiêu của người khác
test('user cannot view others outcomes', function () {
    $otherUser = User::factory()->create();
    // Factory sẽ tự lấy hũ của otherUser để tạo outcome (nhờ logic ta vừa sửa ở trên)
    $outcome = Outcome::factory()->create(['user_id' => $otherUser->id]);

    $response = $this
        ->actingAs($this->user)
        ->getJson("/api/outcomes/{$outcome->id}");

    $response->assertStatus(403);
});

test('cannot add outcome if jar has insufficient balance', function () {
    // 1. Set số dư hũ về 0
    $this->jarNec->update(['balance' => 0]);

    // 2. Cố tình tiêu 50k
    $response = $this
        ->actingAs($this->user)
        ->postJson('/api/outcomes', [
            'jar_id' => $this->jarNec->id,
            'amount' => 50000,
            'category' => 'Shoping',
            'date' => now()->format('Y-m-d'),
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonFragment(['message' => 'Insufficient balance in selected jar.']);
});

test('updating outcome correctly refunds old jar and deducts new jar', function () {
    // 1. Setup: Hũ NEC có 1tr, Hũ FFA có 1tr
    $this->jarNec->update(['balance' => 1000000]);
    $this->jarFfa->update(['balance' => 1000000]);

    // 2. Tạo sẵn 1 chi tiêu 100k ở hũ NEC -> NEC còn 900k
    $outcome = Outcome::factory()->create([
        'user_id' => $this->user->id,
        'jar_id' => $this->jarNec->id,
        'amount' => 100000
    ]);
    $this->jarNec->update(['balance' => 900000]);  // Cập nhật số dư đúng thực tế

    // 3. Gọi API Update: Chuyển chi tiêu này sang hũ FFA
    $response = $this
        ->actingAs($this->user)
        ->putJson("/api/outcomes/{$outcome->id}", [
            'jar_id' => $this->jarFfa->id,  // Đổi sang hũ FFA
            'amount' => 100000,  // Giữ nguyên số tiền
            'date' => now()->format('Y-m-d'),
        ]);

    $response->assertStatus(200);

    // 4. Kiểm tra kết quả:
    // - Hũ NEC phải được hoàn 100k -> Về lại 1tr
    expect((int) $this->jarNec->fresh()->balance)->toEqual(1000000);

    // - Hũ FFA phải bị trừ 100k -> Còn 900k
    expect((int) $this->jarFfa->fresh()->balance)->toEqual(900000);
});
