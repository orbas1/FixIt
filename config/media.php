<?php

return [
    'imgproxy' => [
        'enabled' => env('IMGPROXY_ENABLED', false),
        'endpoint' => env('IMGPROXY_ENDPOINT'),
        'key' => env('IMGPROXY_KEY'),
        'salt' => env('IMGPROXY_SALT'),
        'format' => env('IMGPROXY_FORMAT', 'webp'),
        'quality' => (int) env('IMGPROXY_QUALITY', 82),
    ],
    'variants' => [
        'thumbnail' => [
            'resize' => 'fill',
            'width' => 320,
            'height' => 320,
            'quality' => 80,
        ],
        'card' => [
            'resize' => 'fill',
            'width' => 640,
            'height' => 360,
            'quality' => 82,
        ],
        'gallery' => [
            'resize' => 'fit',
            'width' => 1280,
            'height' => 720,
            'quality' => 85,
        ],
    ],
];
