<?php

    return [
        'name' => 'Firebase',
        'slug' => 'firebase',
        'image' => 'modules/firebase/images/logo.png',
        'notes' => 'This SMS gateway integration is exclusively used for sending OTPs.',
        'configs' => [
            'firebase_api_key' => env('FIREBASE_API_KEY'),
            'firebase_auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
            'firebase_project_id' => env('FIREBASE_PROJECT_ID'),
            'firebase_storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
            'firebase_sender_id' => env('FIREBASE_SENDER_ID'),
            'firebase_app_id' => env('FIREBASE_APP_ID'),
            'firebase_measurement_id' => env('FIREBASE_MEASUREMENT_ID'),
        ],
        'fields' => [
            'firebase_api_key' => [
                'type' => 'password',
                'label' => 'Firebase Api key',
            ],
            'firebase_auth_domain' => [
                'type' => 'password',
                'label' => 'Firebase Auth Domain',
            ],
            'firebase_project_id' => [
                'type' => 'password',
                'label' => 'Firebase Project Id',
            ],
            'firebase_storage_bucket' => [
                'type' => 'password',
                'label' => 'Firebase Storage Bucket',
            ],
            'firebase_sender_id' => [
                'type' => 'password',
                'label' => 'Firebase Sender Id',
            ],
            'firebase_app_id' => [
                'type' => 'password',
                'label' => 'Firebase App Id',
            ],
            'firebase_measurement_id' => [
                'type' => 'password',
                'label' => 'Firebase Measurement Id',
            ],
        ],
    ];
