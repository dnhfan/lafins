<?php

/**
 * @var \Tests\TestCase $this
 * @property \App\Models\User $user
 */

use App\Models\Income;
use App\Models\Outcome;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    // Đảm bảo Observer đã tạo hũ, lấy ra hũ NEC để test chi tiêu
    $this->jarNec = $this->user->jars()->where('name', 'NEC')->first();
});

// 1. Test cấu trúc dữ liệu trả về (Happy Path)
test('authenticated user can load dashboard data', function () {
    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/dashboard');

    $response->assertStatus(200);

    // 👇 SỬA LẠI CẤU TRÚC CHO KHỚP CONTROLLER
    $response->assertJsonStructure([
        'status',
        'message',
        'data' => [
            'summary' => [  // Dữ liệu tổng hợp nằm trong 'summary'
                'total_balance',
                'total_income',
                'total_outcome'
            ],
            'jars' => [  // Danh sách hũ
                '*' => ['id', 'name', 'percentage', 'balance']
            ],
            'jar_meta' => [  // Meta data
                'percent_sum',
                'percent_sum_valid'
            ],
            'filters'
        ]
    ]);
});

// 2. Test tính toán tổng tiền theo bộ lọc ngày (Dùng ngày cố định)
test('dashboard correctly filters income and outcome by date range', function () {
    // A. Setup dữ liệu

    // 1. Dữ liệu "TRONG VÙNG CHECK" (Ngày 15/05/2025)
    Income::factory()->create([
        'user_id' => $this->user->id,
        'amount' => 10000000,  // 10 triệu
        'date' => '2025-05-15'
    ]);

    Outcome::factory()->create([
        'user_id' => $this->user->id,
        'jar_id' => $this->jarNec->id,
        'amount' => 2000000,  // 2 triệu
        'date' => '2025-05-15'
    ]);

    // 2. Dữ liệu "NGOÀI VÙNG CHECK" (Ngày 01/01/2025 - Quá khứ xa)
    Income::factory()->create([
        'user_id' => $this->user->id,
        'amount' => 50000000,  // 50 triệu này KHÔNG ĐƯỢC tính vào
        'date' => '2025-01-01'
    ]);

    // B. Gọi API: Lọc từ 01/05/2025 đến 31/05/2025
    $response = $this
        ->actingAs($this->user)
        ->getJson('/api/dashboard?start=2025-05-01&end=2025-05-31');

    $response->assertStatus(200);

    // Debug: Nếu lỗi, in ra xem nó trả về cái gì
    // dd($response->json());

    $summary = $response->json('data.summary');

    // C. Kiểm tra kết quả
    // Tổng thu chỉ được là 10tr (của tháng 5), không được lẫn 50tr (của tháng 1)
    expect((int) $summary['total_income'])->toEqual(10000000);
    expect((int) $summary['total_outcome'])->toEqual(2000000);
});

// 3. Test Tổng số dư (Total Balance)
test('dashboard calculates total balance from all jars', function () {
    // Set tiền cho các hũ
    $jars = $this->user->jars;
    foreach ($jars as $jar) {
        $jar->update(['balance' => 100000]);  // Mỗi hũ 100k
    }

    $totalExpected = 100000 * $jars->count();

    $response = $this->actingAs($this->user)->getJson('/api/dashboard');

    // 👇 SỬA LẠI ĐƯỜNG DẪN
    $summary = $response->json('data.summary');

    expect((int) $summary['total_balance'])->toEqual($totalExpected);
});
