<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Jar;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Create default jars for the user using config/jars.php percentages
        $defaults = config('jars.defaults', []);

        if (empty($defaults)) {
            return;
        }

        foreach ($defaults as $name => $percent) {
            Jar::firstOrCreate(
                ['user_id' => $user->id, 'name' => $name],
                ['percentage' => $percent, 'balance' => 0]
            );
        }
    }
}
