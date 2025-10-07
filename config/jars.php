<?php

return [
    // Default jar percentage allocations (used when creating a new user)
    // Keys must match App\Models\Jar::JAR_TYPES keys
    'defaults' => [
        'NEC' => 55,
        'FFA' => 10,
        'EDU' => 10,
        'LTSS' => 10,
        'PLAY' => 10,
        'GIVE' => 5,
    ],
];
