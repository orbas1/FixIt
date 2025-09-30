<?php

return [
    'fallback' => [
        'latitude' => env('LOCATION_FALLBACK_LAT', 37.773972),
        'longitude' => env('LOCATION_FALLBACK_LNG', -122.431297),
        'city' => env('LOCATION_FALLBACK_CITY', 'San Francisco'),
        'region' => env('LOCATION_FALLBACK_REGION', 'CA'),
        'country' => env('LOCATION_FALLBACK_COUNTRY', 'United States'),
    ],
    'ip_lookup_endpoint' => env('LOCATION_IP_LOOKUP_ENDPOINT', 'https://ipapi.co/%s/json/'),
];
