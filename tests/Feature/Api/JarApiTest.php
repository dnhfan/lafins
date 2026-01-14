<?php

use App\Models\Jar;
use App\Models\User;

// 1. Test Index: User tạo xong là có hũ luôn, không cần factory tạo thêm
test('authenticated user can list their jars', function () {
    $user = User::factory()->create();
    // Logic app tự tạo 6 hũ, nên không cần Jar::factory()->create() ở đây nữa

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson('/api/jars');

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'jars' => [
                    '*' => ['id', 'name', 'percentage', 'balance']
                ]
            ]
        ]);

    // Kiểm tra xem có đúng 6 hũ mặc định không
    expect($response->json('data.jars'))->toHaveCount(6);
});

// 2. Test Update: Lấy hũ có sẵn để sửa
test('user can update jar percentages successfully', function () {
    $user = User::factory()->create();

    // Lấy 2 hũ đầu tiên trong danh sách tự tạo của user
    $jar1 = $user->jars()->where('name', 'NEC')->first();
    $jar2 = $user->jars()->where('name', 'PLAY')->first();

    // Nếu không tìm thấy tên cụ thể thì lấy theo index (fallback)
    if (!$jar1)
        $jar1 = $user->jars[0];
    if (!$jar2)
        $jar2 = $user->jars[1];

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson('/api/jars/bulk-update', [
            'percentages' => [
                $jar1->id => 70,
                $jar2->id => 30,
                // Các hũ còn lại user không gửi lên thì server phải tự hiểu là giữ nguyên hoặc reset về 0 tùy logic
                // Ở đây ta giả định server chỉ update cái gửi lên,
                // NHƯNG thường bulk update cần tổng 100%.
                // Để test này xanh, ta cần update sao cho tổng các hũ còn lại + 70 + 30 = 100 ??
                // HOẶC logic của bro là gửi cái gì update cái đó?
                // -> Giả sử logic là update 2 cái này, server handle phần còn lại.
            ]
        ]);

    // LƯU Ý: Nếu logic validate bắt buộc tổng 100%, bro phải lấy hết ID các hũ ra để chia lại.
    // Dưới đây là cách an toàn nhất: Set 2 cái, các cái còn lại set 0
    $allJars = $user->jars;
    $payload = [];
    foreach ($allJars as $jar) {
        if ($jar->id === $jar1->id)
            $payload[$jar->id] = 70;
        elseif ($jar->id === $jar2->id)
            $payload[$jar->id] = 30;
        else
            $payload[$jar->id] = 0;
    }

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson('/api/jars/bulk-update', [
            'percentages' => $payload
        ]);

    $response->assertStatus(200);

    expect($jar1->fresh()->percentage)->toBe(70.0);
    expect($jar2->fresh()->percentage)->toBe(30.0);
});

// 3. Test Validation: Tổng != 100
test('update fails if total percentage is not 100', function () {
    $user = User::factory()->create();
    $jar1 = $user->jars->first();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson('/api/jars/bulk-update', [
            'percentages' => [
                $jar1->id => 90  // Mới có 90%, thiếu 10%
            ]
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonStructure(['error' => ['percentages']]);
});

// 4. Test Validation: Số âm
test('update fails with negative percentage', function () {
    $user = User::factory()->create();
    $jar1 = $user->jars[0];
    $jar2 = $user->jars[1];

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson('/api/jars/bulk-update', [
            'percentages' => [
                $jar1->id => 110,
                $jar2->id => -10
            ]
        ]);

    $response->assertStatus(422);
});

// 5. FIX LỖI UNIQUE: User A sửa hũ User B
test('user cannot update jars belonging to others', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    // Lấy đại 1 cái hũ của B (Đừng tạo mới, nó trùng tên đấy!)
    $jarOfB = $userB->jars->first();

    $response = $this
        ->actingAs($userA, 'sanctum')
        ->postJson('/api/jars/bulk-update', [
            'percentages' => [
                $jarOfB->id => 100
            ]
        ]);

    $response
        ->assertStatus(422)
        ->assertJson(['status' => 'error']);
});

// 6. Test Guest
test('guest cannot view jars', function () {
    $this->getJson('/api/jars')->assertStatus(401);
});
