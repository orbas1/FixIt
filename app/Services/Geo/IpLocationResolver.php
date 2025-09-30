<?php

namespace App\Services\Geo;

use GuzzleHttp\ClientInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class IpLocationResolver
{
    public function __construct(private ClientInterface $httpClient)
    {
    }

    public function resolve(string $ip): ?array
    {
        $cacheKey = sprintf('geo:ip:%s', $ip);
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($ip) {
            $endpoint = Config::get('location.ip_lookup_endpoint', 'https://ipapi.co/%s/json/');
            $url = sprintf($endpoint, $ip === '127.0.0.1' ? '' : $ip);

            $response = $this->httpClient->request('GET', $url, [
                'timeout' => 5,
                'connect_timeout' => 5,
            ]);

            if ($response->getStatusCode() >= 400) {
                return null;
            }

            $payload = json_decode((string) $response->getBody(), true);
            if (! is_array($payload)) {
                return null;
            }

            $latitude = Arr::get($payload, 'latitude') ?? Arr::get($payload, 'lat');
            $longitude = Arr::get($payload, 'longitude') ?? Arr::get($payload, 'lon');
            if ($latitude === null || $longitude === null) {
                return null;
            }

            return [
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude,
                'city' => Arr::get($payload, 'city'),
                'region' => Arr::get($payload, 'region'),
                'country' => Arr::get($payload, 'country_name') ?? Arr::get($payload, 'country'),
            ];
        });
    }
}
