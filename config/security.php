<?php

return [
    'mfa' => [
        'challenge_ttl' => 600, // seconds
        'totp_window' => 1,
        'recovery_codes' => 10,
        'max_attempts' => 5,
    ],
];
