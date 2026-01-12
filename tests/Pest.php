<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * |--------------------------------------------------------------------------
 * | Test Case Configuration
 * |--------------------------------------------------------------------------
 * |
 * | Dòng dưới đây cực kỳ quan trọng. Nó bảo Pest rằng tất cả các file test
 * | nằm trong thư mục "Feature" sẽ được thừa hưởng các tính năng của Laravel.
 * |
 */

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

/*
 * |--------------------------------------------------------------------------
 * | Expectations (Tùy chọn)
 * |--------------------------------------------------------------------------
 * |
 * | Nơi này để ông định nghĩa các hàm kiểm tra riêng nếu muốn code ngắn hơn.
 * | Ví dụ: expect($value)->toBeNumeric();
 * |
 */

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
 * |--------------------------------------------------------------------------
 * | Helpers
 * |--------------------------------------------------------------------------
 * |
 * | Nơi định nghĩa các hàm helper dùng chung cho toàn bộ các file test.
 * |
 */

function actingAsUser()
{
    return test()->actingAs(\App\Models\User::factory()->create(), 'sanctum');
}
