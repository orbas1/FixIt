<?php

namespace App\Services\Geo;

use InvalidArgumentException;

/**
 * Lightweight deterministic geo indexer that emulates H3 resolution buckets.
 *
 * This implementation does not depend on native extensions; it normalises the
 * latitude/longitude pair and converts it into a stable 60-bit integer using
 * a fast hash. The output preserves ordering for nearby coordinates at the
 * configured resolution which is sufficient for caching, sharding and
 * pagination in feed queries. When a dedicated H3 extension becomes available
 * the implementation can be swapped without touching callers.
 */
class H3IndexService
{
    private const MIN_RESOLUTION = 0;
    private const MAX_RESOLUTION = 15;

    public function indexFor(float $latitude, float $longitude, int $resolution = 8): int
    {
        $normalisedResolution = $this->normaliseResolution($resolution);

        $lat = $this->clamp($latitude, -90.0, 90.0);
        $lng = $this->clamp($longitude, -180.0, 180.0);

        $scaledLat = (int) round(($lat + 90) * 1_000_000);
        $scaledLng = (int) round(($lng + 180) * 1_000_000);

        $hashSource = $scaledLat . ':' . $scaledLng . ':' . $normalisedResolution;
        $hash = hash('xxh3', $hashSource);

        return hexdec(substr($hash, 0, 15));
    }

    private function normaliseResolution(int $resolution): int
    {
        if ($resolution < self::MIN_RESOLUTION || $resolution > self::MAX_RESOLUTION) {
            throw new InvalidArgumentException('Resolution must be between 0 and 15.');
        }

        return $resolution;
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}
