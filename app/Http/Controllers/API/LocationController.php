<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Geo\H3IndexService;
use App\Services\Geo\IpLocationResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class LocationController extends Controller
{
    public function __construct(
        private readonly IpLocationResolver $resolver,
        private readonly H3IndexService $indexService,
    ) {
    }

    public function estimate(Request $request): JsonResponse
    {
        $ip = $request->ip();
        $resolved = $this->resolver->resolve($ip ?? '');

        if ($resolved === null) {
            $fallback = Config::get('location.fallback');
            $resolved = [
                'latitude' => (float) ($fallback['latitude'] ?? 0.0),
                'longitude' => (float) ($fallback['longitude'] ?? 0.0),
                'city' => $fallback['city'] ?? null,
                'region' => $fallback['region'] ?? null,
                'country' => $fallback['country'] ?? null,
                'source' => 'fallback',
            ];
        } else {
            $resolved['source'] = 'ip_lookup';
        }

        $resolved['h3_index'] = $this->indexService->indexFor(
            $resolved['latitude'],
            $resolved['longitude'],
            config('services.h3.feed_resolution', 8)
        );

        return response()->json([
            'data' => $resolved,
        ]);
    }
}
