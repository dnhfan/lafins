<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Income;
use Carbon\Carbon;

class IncomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        if (! $user) {
            $this->command->info('No users found; please run DatabaseSeeder to create a user first.');
            return;
        }

        // Create few incomes across recent months
        $samples = [
            ['amount' => 15000000, 'source' => 'Salary', 'date' => Carbon::now()->subMonths(2)->toDateString()],
            ['amount' => 16000000, 'source' => 'Salary', 'date' => Carbon::now()->subMonth()->toDateString()],
            ['amount' => 17000000, 'source' => 'Salary', 'date' => Carbon::now()->toDateString()],
            ['amount' => 500000, 'source' => 'Freelance', 'date' => Carbon::now()->subDays(10)->toDateString()],
        ];

        foreach ($samples as $s) {
            Income::create([
                'user_id' => $user->id,
                'amount' => $s['amount'],
                'source' => $s['source'],
                'description' => 'Seeded income',
                'date' => $s['date'],
            ]);
        }
    }
}
