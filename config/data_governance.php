<?php

return [
    'classifications' => [
        'public',
        'internal',
        'confidential',
        'restricted',
    ],

    'lawful_bases' => [
        'consent',
        'contract',
        'legal_obligation',
        'vital_interest',
        'public_task',
        'legitimate_interest',
    ],

    'residency_policies' => [
        'regions' => [
            'US' => ['US'],
            'EU' => ['AT', 'BE', 'DE', 'FR', 'IE', 'NL', 'PL', 'PT', 'ES', 'SE'],
            'APAC' => ['SG', 'AU', 'NZ', 'JP', 'IN'],
            'LATAM' => ['BR', 'MX', 'CL', 'AR'],
            'MEA' => ['AE', 'ZA', 'KE'],
        ],
        'encryption_profiles' => [
            'aes-256',
            'kms-managed',
            'hsm-backed',
        ],
    ],

    'dpia' => [
        'risk_threshold' => 65,
        'default_review_months' => 12,
        'auto_assign_permissions' => [
            'backend.compliance.data_governance.manage',
            'backend.setting.edit',
        ],
    ],
];
