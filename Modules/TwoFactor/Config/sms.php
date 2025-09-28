<?php

    return [
        'name' => 'TwoFactor',
        'slug' => 'twoFactor',
        'image' => 'modules/twoFactor/images/logo.png',
        'notes' => '',
        'configs' => [
            'twoFactor_key' => env('TWOFACTOR_API_KEY'),
            'sender_id' => env('TWOFACTOR_SENDER_ID'),
            'template_name' => env('TWOFACTOR_TEMPLATE_NAME'),
        ],
        'fields' => [
            'twoFactor_key' => [
                'type' => 'password',
                'label' => 'TwoFactor Key',
            ],
            'sender_id' => [
                'type' => 'password',
                'label' => 'TwoFactor Sender Id',
            ],
            'template_name' => [
                'type' => 'password',
                'label' => 'TwoFactor Template Name',
            ],
        ],
    ];
