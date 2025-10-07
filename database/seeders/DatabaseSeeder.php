<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure a test user exists (idempotent)
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Run seeders that depend on a user being present. These seeders are idempotent
        // or safe (use updateOrCreate/create) to avoid duplicates/conflicts.
        // Run Income first so we can distribute received income into jars equally,
        // then create jars (balances will be calculated from incomes - outcomes),
        // and finally create outcomes which will consume from jars without allowing
        // any jar to go negative.
        $this->call([
            IncomeSeeder::class,
            JarSeeder::class,
            OutcomeSeeder::class,
        ]);
    }
}
