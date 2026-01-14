<?php

namespace Database\Factories;

use App\Models\Jar;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Outcome>
 */
class OutcomeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 1. Tạo User mới (Observer sẽ chạy và tạo 6 hũ cho user này)
            'user_id' => User::factory(),
            // 2. Lấy ngẫu nhiên 1 hũ thuộc về user đó để gán vào Outcome
            // Dùng closure function để truy cập được attributes['user_id']
            'jar_id' => function (array $attributes) {
                return Jar::where('user_id', $attributes['user_id'])
                    ->inRandomOrder()
                    ->first()
                    ->id;
            },
            'amount' => $this->faker->numberBetween(1, 500) * 10000,
            'description' => $this->faker->sentence(),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
        ];
    }
}
