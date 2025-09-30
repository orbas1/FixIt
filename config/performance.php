<?php

return [
    'query_budget' => [
        'enabled' => env('PERFORMANCE_QUERY_BUDGET_ENABLED', true),
        'default_max_queries' => env('PERFORMANCE_DEFAULT_QUERY_BUDGET', 25),
        'slow_query_threshold_ms' => env('PERFORMANCE_SLOW_QUERY_THRESHOLD_MS', 150),
        'routes' => [
            'api.feed.service-requests' => 2,
            'feed/service-requests' => 2,
        ],
    ],
    'latency_budget_ms' => [
        'api' => env('PERFORMANCE_API_LATENCY_BUDGET_MS', 400),
    ],
];
