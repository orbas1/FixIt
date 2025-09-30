<?php

return [
    'secrets' => [
        'cache_ttl' => env('SECRET_VAULT_CACHE_TTL', 300),
        'rotation_days' => env('SECRET_ROTATION_DAYS', 90),
    ],
    'privacy' => [
        'default_preferences' => [
            'essential' => true,
            'marketing' => false,
            'analytics' => false,
        ],
    ],
];
