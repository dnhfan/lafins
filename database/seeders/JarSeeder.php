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

        // Allocate the total balance according to configured percentages.
        // To avoid rounding errors, compute the floor allocation for each jar
        // and distribute any remainder to jars with the largest fractional parts.
        $allocations = [];
        $fractionals = [];
        $totalAllocated = 0;

        if ($totalBalance <= 0) {
            // Nothing to allocate
            foreach ($defaults as $name => $percent) {
                $allocations[$name] = 0;
            }
        } else {
            // First pass: compute floor allocations and fractional parts
            foreach ($defaults as $name => $percent) {
                $floatAlloc = ($totalBalance * $percent) / 100;
                $floorAlloc = (int) floor($floatAlloc);
                $allocations[$name] = $floorAlloc;
                $fractionals[$name] = $floatAlloc - $floorAlloc;
                $totalAllocated += $floorAlloc;
            }

            // Distribute any remaining units to jars with highest fractional parts
            $remainder = $totalBalance - $totalAllocated;
            if ($remainder > 0) {
                // Build a sortable list preserving original order for tie-breaking
                $idx = 0;
                $fracList = [];
                foreach ($defaults as $name => $percent) {
                    $fracList[] = ['name' => $name, 'fraction' => $fractionals[$name], 'index' => $idx++];
                }

                usort($fracList, function ($a, $b) {
                    if ($a['fraction'] === $b['fraction']) {
                        return $a['index'] <=> $b['index'];
                    }
                    return $b['fraction'] <=> $a['fraction'];
                });

                $i = 0;
                while ($remainder > 0 && $i < count($fracList)) {
                    $allocations[$fracList[$i]['name']]++;
                    $remainder--;
                    $i++;
                }
            }
        }

        // Persist jars with percent-based allocations
        foreach ($defaults as $name => $percent) {
            $allocated = $allocations[$name] ?? 0;

            Jar::updateOrCreate(
                ['user_id' => $user->id, 'name' => $name],
                ['percentage' => $percent, 'balance' => $allocated]
            );
        }
    }
}
