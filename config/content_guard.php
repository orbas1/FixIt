<?php

return [
    'lexicons' => [
        'en' => [
            'block' => [
                'terrorist',
                'bomb',
                'child\s*exploitation',
                'extortion',
            ],
            'review' => [
                'bitcoin',
                'wire transfer',
                'western union',
            ],
        ],
    ],
    'link' => [
        'ttl' => 3600,
    ],
    'patterns' => [
        'email' => '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i',
        'phone' => '/(\+?\d{1,3}[\s.-]?)?(\(\d{2,4}\)|\d{2,4})[\s.-]?\d{3,4}[\s.-]?\d{3,4}/',
        'ip' => '/(?<![\w])(\d{1,3}\.){3}\d{1,3}(?![\w])/',
        'url' => '/https?:\/\/[^\s<]+/i',
    ],
];
