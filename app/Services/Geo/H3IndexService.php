<?php

namespace App\Services\Geo;

use InvalidArgumentException;

/**
 * Lightweight deterministic geo indexer that emulates H3 resolution buckets.
 *
 * This implementation does not depend on native extensions; it normalises the
 * latitude/longitude pair and converts it into a stable 60-bit integer by
 * quantising the coordinates to a resolution-aware grid. Nearby coordinates
 * therefore share identical buckets which keeps cache keys, sharding and
 * pagination stable across database vendors. When a dedicated H3 extension
 * becomes available the implementation can be swapped without touching
 * callers.
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

        $scale = $this->bucketScale($normalisedResolution);

        $latBucket = (int) floor(($lat + 90.0) * $scale);
        $lngBucket = (int) floor(($lng + 180.0) * $scale);

        return ($latBucket << 32) | ($lngBucket & 0xFFFFFFFF);
    }

    /**
     * Generate approximate neighbour indexes for a latitude/longitude pair
     * constrained by a radius in kilometres. This is a lightweight shim until
     * the native H3 extension is available in the runtime.
     *
     * @return array<int>
     */
    public function indexesForRadius(float $latitude, float $longitude, float $radiusKm, int $resolution = 8): array
    {
        $radiusKm = max(0.1, $radiusKm);
        $degreeRadius = $radiusKm / 111.0; // approximate degrees per kilometre
        $density = max(6, (int) ceil($radiusKm * 2));
        $scale = $this->bucketScale($resolution);
        $minimumStep = 1.0 / $scale;
        $stepSize = max($minimumStep, min(1.0, $degreeRadius / max($density * 32, 1)));

        $indexes = [
            $this->indexFor($latitude, $longitude, $resolution),
        ];

        for ($latStep = -$density; $latStep <= $density; $latStep++) {
            for ($lngStep = -$density; $lngStep <= $density; $lngStep++) {
                if ($latStep === 0 && $lngStep === 0) {
                    continue;
                }

                $offsetLat = $latitude + ($latStep * $stepSize);
                $cosine = max(cos(deg2rad($offsetLat)), 0.1);
                $offsetLng = $longitude + ($lngStep * $stepSize / $cosine);

                $indexes[] = $this->indexFor($offsetLat, $offsetLng, $resolution);
            }
        }

        return array_values(array_unique($indexes));
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

    private function bucketScale(int $resolution): float
    {
        return 1_000.0 * (2 ** $resolution);
    }
}
