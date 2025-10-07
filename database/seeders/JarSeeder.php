<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Jar;
use App\Models\Income;
use App\Models\Outcome;

class JarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure a user exists
        $user = User::first();
        if (! $user) {
            $this->command->info('No users found; please run DatabaseSeeder to create a user first.');
            return;
        }

        // Default jar percentages (matches Jar::JAR_TYPES semantics)
        $defaults = [
            'NEC' => 55,
            'FFA' => 10,
            'EDU' => 10,
            'LTSS' => 10,
            'PLAY' => 10,
            'GIVE' => 5,
        ];
        // Calculate totals from Income and Outcome seeders that already ran.
        $totalIncome = Income::where('user_id', $user->id)->sum('amount');
        $totalOutcome = Outcome::where('user_id', $user->id)->sum('amount');

        // Ensure total balance equals income - outcome (can be zero)
        $totalBalance = max(0, $totalIncome - $totalOutcome);

        $jarCount = count($defaults);

        // Distribute income equally across jars. Use integer division with rounding.
        $equalShare = floor($totalBalance / $jarCount);
        $remainder = $totalBalance - ($equalShare * $jarCount);

        $i = 0;
        foreach ($defaults as $name => $percent) {
            // Give the remainder to the first few jars to ensure sum matches totalBalance
            $allocated = $equalShare + ($i < $remainder ? 1 : 0);

            // If equal allocation produced zero (very small total), fall back to percent-based allocation
            if ($allocated <= 0) {
                $allocated = (int) round(($totalBalance * $percent) / 100);
            }

            Jar::updateOrCreate(
                ['user_id' => $user->id, 'name' => $name],
                ['percentage' => $percent, 'balance' => $allocated]
            );

            $i++;
        }
    }
}
