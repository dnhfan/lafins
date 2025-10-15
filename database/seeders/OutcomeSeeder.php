<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Outcome;
use App\Models\Jar;
use Carbon\Carbon;

class OutcomeSeeder extends Seeder
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

        // Try to find jars to associate outcomes, otherwise jar_id = null
        $jarMap = Jar::where('user_id', $user->id)->get()->keyBy('name');

        $samples = [
            ['amount' => 120000, 'category' => 'food', 'jar' => 'NEC', 'date' => Carbon::now()->subDays(3)->toDateString()],
            ['amount' => 250000, 'category' => 'transport', 'jar' => 'NEC', 'date' => Carbon::now()->subDays(7)->toDateString()],
            ['amount' => 500000, 'category' => 'entertainment', 'jar' => 'PLAY', 'date' => Carbon::now()->subDays(15)->toDateString()],
            ['amount' => 1000000, 'category' => 'education', 'jar' => 'EDU', 'date' => Carbon::now()->subMonth()->toDateString()],
        ];

        foreach ($samples as $s) {
            $jar = $jarMap->has($s['jar']) ? $jarMap->get($s['jar']) : null;
            $amount = (int) $s['amount'];

            // If the outcome is tied to a jar, ensure we don't spend more than the jar has.
            if ($jar) {
                // Reload latest balance to be safe
                $jar->refresh();

                if ($jar->balance <= 0) {
                    // Nothing to spend from this jar
                    $this->command->info(sprintf('Skipping outcome for jar %s because balance is zero', $jar->name));
                    continue;
                }

                if ($amount > $jar->balance) {
                    // Clamp to available balance
                    $this->command->info(sprintf('Clamping outcome for jar %s from %d to %d', $jar->name, $amount, $jar->balance));
                    $amount = $jar->balance;
                }
            }

            $outcome = Outcome::create([
                'user_id' => $user->id,
                'jar_id' => $jar ? $jar->id : null,
                'amount' => $amount,
                'category' => $s['category'],
                'description' => 'Seeded outcome',
                'date' => $s['date'],
            ]);

            // Deduct the amount from the jar balance to keep consistency
            if ($jar && $amount > 0) {
                $jar->balance = max(0, $jar->balance - $amount);
                $jar->save();
            }
        }

        // Generate many outcomes for pagination testing
        $categories = ['food', 'transport', 'entertainment', 'education', 'health', 'shopping', 'bills', 'other'];
        $now = Carbon::now();

        // We'll attempt to create 300 outcomes. If jars run out of balance, outcomes may be created without a jar.
        for ($i = 0; $i < 300; $i++) {
            $category = $categories[$i % count($categories)];
            $amount = random_int(50000, 2000000); // between 50k and 2M

            // Choose a jar randomly from available jars or null
            $jarNames = $jarMap->keys()->all();
            $jar = null;
            if (!empty($jarNames) && random_int(0, 100) < 80) { // 80% of outcomes tied to a jar
                $chosen = $jarNames[array_rand($jarNames)];
                $jar = $jarMap->get($chosen);
                $jar->refresh();

                if ($jar->balance <= 0) {
                    $jar = null;
                } else {
                    if ($amount > $jar->balance) {
                        $amount = $jar->balance;
                    }
                }
            }

            // Spread dates across the last 12 months
            $daysAgo = (int) floor(($i / 300) * 365);
            $date = $now->copy()->subDays($daysAgo)->subDays(random_int(0, 30))->toDateString();

            if ($amount <= 0) {
                continue;
            }

            $outcome = Outcome::create([
                'user_id' => $user->id,
                'jar_id' => $jar ? $jar->id : null,
                'amount' => $amount,
                'category' => $category,
                'description' => 'Seeded outcome for pagination test',
                'date' => $date,
            ]);

            if ($jar && $amount > 0) {
                $jar->balance = max(0, $jar->balance - $amount);
                $jar->save();
            }
        }
    }
}
