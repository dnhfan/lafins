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

        // Create few incomes across recent months (keep a small set for clarity)
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

        // Add many additional incomes for pagination testing
        $sources = ['Salary', 'Freelance', 'Investment', 'Gift', 'Bonus', 'Other'];
        $now = Carbon::now();

        // Generate 300 incomes spread over the last 12 months with varied amounts/sources
        for ($i = 0; $i < 300; $i++) {
            // Randomize amount roughly between 100k and 20,000,000
            $amount = random_int(100000, 20000000);

            // Pick a source cycling through the list
            $source = $sources[$i % count($sources)];

            // Spread dates across the last 12 months
            $daysAgo = (int) floor(($i / 300) * 365); // distribute across ~1 year
            $date = $now->copy()->subDays($daysAgo)->subDays(random_int(0, 30))->toDateString();

            Income::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'source' => $source,
                'description' => 'Seeded income for pagination test',
                'date' => $date,
            ]);
        }
    }
}
