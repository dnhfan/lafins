<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Income>
 */
class IncomeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Tao lien ket voi user
            'user_id' => User::factory(),
            // Tao so tien random
            'amount' => $this->faker->numberBetween(10, 5000) * 10000,
            // Nguồn thu nhập (Ví dụ: Lương, Thưởng, Bán hàng...)
            'source' => $this->faker->randomElement(['Salary', 'Bonus', 'Freelance', 'Investment', 'Gift']),
            // Mô tả ngẫu nhiên
            'description' => $this->faker->sentence(),
            // Ngày thu nhập trong vòng 1 năm trở lại đây
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }
}
