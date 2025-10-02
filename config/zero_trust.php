<?php

return [
    'policy_version' => env('ZERO_TRUST_POLICY_VERSION', '2024.07'),

    'risk' => [
        'baseline' => (int) env('ZERO_TRUST_BASELINE_SCORE', 5),
        'new_device_penalty' => (int) env('ZERO_TRUST_NEW_DEVICE_PENALTY', 35),
        'stale_mfa_minutes' => (int) env('ZERO_TRUST_STALE_MFA_MINUTES', 720),
        'stale_mfa_penalty' => (int) env('ZERO_TRUST_STALE_MFA_PENALTY', 25),
        'untrusted_network_penalty' => (int) env('ZERO_TRUST_UNTRUSTED_NETWORK_PENALTY', 30),
        'unknown_network_penalty' => (int) env('ZERO_TRUST_UNKNOWN_NETWORK_PENALTY', 15),
        'impossible_travel_threshold_minutes' => (int) env('ZERO_TRUST_IMPOSSIBLE_TRAVEL_MINUTES', 120),
        'impossible_travel_penalty' => (int) env('ZERO_TRUST_IMPOSSIBLE_TRAVEL_PENALTY', 40),
        'deny_threshold' => (int) env('ZERO_TRUST_DENY_THRESHOLD', 85),
        'challenge_threshold' => (int) env('ZERO_TRUST_CHALLENGE_THRESHOLD', 55),
    ],

    'enforcement' => [
        'require_mfa_roles' => array_filter(explode(',', env('ZERO_TRUST_REQUIRE_MFA_ROLES', 'admin,finance,ops'))),
        'trusted_device_ttl_days' => (int) env('ZERO_TRUST_DEVICE_TTL_DAYS', 90),
        'default_controls' => [
            'mfa',
            'device_trust',
            'network_segmentation',
        ],
    ],

    'telemetry' => [
        'evidence_channel' => env('ZERO_TRUST_EVIDENCE_CHANNEL', 'security.zero_trust'),
        'retention_days' => (int) env('ZERO_TRUST_EVIDENCE_RETENTION_DAYS', 365),
    ],
];
