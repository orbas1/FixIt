<?php

return [
    'gateway' => env('ESCROW_GATEWAY', 'stripe'),
    'release_sla_hours' => (int) env('ESCROW_RELEASE_SLA_HOURS', 72),
    'logging_channel' => env('ESCROW_LOG_CHANNEL', 'stack'),

    'stripe' => [
        'capture_method' => env('ESCROW_STRIPE_CAPTURE_METHOD', 'manual'),
        'zero_decimal_currencies' => [
            'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf',
        ],
    ],
];
