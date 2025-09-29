<?php

namespace Tests\Unit\Tax;

use App\Models\Service;
use App\Models\Tax;
use App\Services\Tax\TaxEngine;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TaxEngineTest extends TestCase
{
    public function test_calculates_breakdown_and_caches_rates(): void
    {
        config(['cache.default' => 'array']);
        Cache::store()->clear();

        $service = new Service();
        $service->id = 99;
        $service->setRelation('taxes', collect([
            new Tax(['id' => 1, 'name' => 'GST', 'rate' => 10, 'zone_id' => 1]),
            new Tax(['id' => 2, 'name' => 'PST', 'rate' => 5, 'zone_id' => 2]),
        ]));

        $engine = app(TaxEngine::class);

        $result = $engine->calculateForService($service, 100, 10);

        $this->assertSame(16.5, $result['total']);
        $this->assertCount(2, $result['lines']);
        $this->assertSame(110.0, $result['taxable_amount']);

        $cacheKey = (new \ReflectionClass(TaxEngine::class))->getConstant('CACHE_PREFIX') . $service->getKey();
        $this->assertNotNull(Cache::get($cacheKey));
    }
}
