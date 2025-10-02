<?php

return [
    'secrets' => [
        'cache_ttl' => env('SECRET_VAULT_CACHE_TTL', 300),
        'rotation_days' => env('SECRET_ROTATION_DAYS', 90),
    ],
    'mfa' => [
        'challenge_ttl' => env('SECURITY_MFA_CHALLENGE_TTL', 300),
        'max_attempts' => env('SECURITY_MFA_MAX_ATTEMPTS', 5),
        'recovery_code_count' => env('SECURITY_MFA_RECOVERY_CODE_COUNT', 10),
        'enrollment_window_minutes' => env('SECURITY_MFA_ENROLLMENT_WINDOW', 10),
    ],
    'privacy' => [
        'default_preferences' => [
            'essential' => true,
            'marketing' => false,
            'analytics' => false,
        ],
    ],
];
