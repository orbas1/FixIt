<?php

namespace App\Services\Tax;

use App\Models\Service;
use App\Models\Tax;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TaxEngine
{
    private const CACHE_PREFIX = 'tax:service:rates:';

    public function calculateForService(Service $service, float $subtotal, float $platformFees = 0.0, array $context = []): array
    {
        $taxableAmount = max(0, round($subtotal + $platformFees, 2));

        $rates = $this->resolveRates($service, $context);

        $lines = $rates->map(function (Tax $tax) use ($taxableAmount) {
            $rate = (float) $tax->rate;
            $amount = round(($taxableAmount * $rate) / 100, 2);

            return [
                'id' => $tax->id,
                'name' => $tax->name,
                'rate' => $rate,
                'amount' => $amount,
                'zone_id' => $tax->zone_id,
            ];
        })->filter(fn (array $line) => $line['amount'] > 0);

        $total = $lines->sum('amount');

        return [
            'total' => round($total, 2),
            'lines' => $lines->values()->all(),
            'taxable_amount' => $taxableAmount,
        ];
    }

    public function invalidateForService(Service $service): void
    {
        Cache::forget($this->cacheKey($service));
    }

    protected function resolveRates(Service $service, array $context): Collection
    {
        $cacheKey = $this->cacheKey($service);

        return Cache::remember($cacheKey, (int) config('services.tax.cache_ttl', 600), function () use ($service) {
            $taxes = $service->relationLoaded('taxes') ? $service->taxes : $service->taxes()->get();

            return $taxes->map(function (Tax $tax) {
                $tax->rate = (float) $tax->rate;
                return $tax;
            });
        });
    }

    protected function cacheKey(Service $service): string
    {
        return self::CACHE_PREFIX . $service->getKey();
    }
}
