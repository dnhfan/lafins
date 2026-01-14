<?php

/**
 * * @var \Tests\TestCase $this
 * @property \App\Models\User $user
 * @property \App\Models\Jar $jarNec
 * @property \App\Models\Jar $jarFfa
 */

use App\Models\Income;
use App\Models\Jar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    // 2. Lấy hũ ra bằng name để tí nữa mình so khớp số dư (balance)
    $this->jarNec = Jar::where('user_id', $this->user->id)->where('name', 'NEC')->first();
    $this->jarFfa = Jar::where('user_id', $this->user->id)->where('name', 'FFA')->first();
});

// 1. Test liệt kê danh sách thu nhập
test('authenticated user can list their incomes', function () {
    Income::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/incomes');

    $response->dump();

    $response
        ->assertStatus(200);
    // Thay vì số 3 cứng nhắc, hãy đếm số lượng thực tế của User này trong DB
    $response->assertJsonCount(3, 'data.incomes.data');
});

// 2. Test thêm thu nhập và TỰ ĐỘNG CHIA TIỀN
test('adding income correctly redistributes money to jars', function () {
    $amount = 1000000;  // 1 triệu
    $incomeData = [
        'amount' => $amount,
        'description' => 'Bonus',
        'date' => now()->format('Y-m-d'),
        'source' => 'Work'
    ];

    $response = $this
        ->actingAs($this->user)
        ->postJson('/api/incomes', $incomeData);

    $response->assertStatus(201);

    // Tính toán số tiền mong đợi dựa trên % thực tế của hũ
    $expectedNec = (int) ($amount * ($this->jarNec->percentage / 100));

    // Kiểm tra tiền trong hũ NEC sau khi thêm thu nhập
    expect((int) $this->jarNec->fresh()->balance)->toEqual($expectedNec);
});

// 3. Test bảo mật: Không được xem thu nhập của người khác
test('user cannot view others incomes', function () {
    $otherUser = User::factory()->create();
    $income = Income::factory()->create(['user_id' => $otherUser->id]);

    $response = $this
        ->actingAs($this->user)
        ->getJson("/api/incomes/{$income->id}");

    $response->assertStatus(403);  // Hoặc 404 tùy logic của bạn
});

// 4. Test Validation: Không cho phép số tiền âm
test('cannot add income with negative amount', function () {
    $response = $this
        ->actingAs($this->user)
        ->postJson('/api/incomes', [
            'amount' => -5000,
            'date' => now()->format('Y-m-d')
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['amount']);
});
