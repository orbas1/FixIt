<?php

namespace Tests\Unit\Services\Security;

use App\Models\Secret;
use App\Services\Security\SecretVaultService;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretVaultServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('cache.default', 'array');
    }

    public function test_it_puts_and_gets_secret(): void
    {
        $service = $this->makeService();
        $key = 'integration/stripe';
        $payload = ['secret' => Str::random(12)];

        $service->put($key, $payload);

        $value = $service->get($key);

        $this->assertSame($payload, $value);

        $secret = Secret::where('key', $key)->firstOrFail();
        $this->assertNotNull($secret->rotated_at);
        $this->assertTrue(Crypt::decryptString($secret->getRawOriginal('value')) !== $secret->getRawOriginal('value'));
    }

    public function test_it_rotates_secret_and_bumps_version(): void
    {
        $service = $this->makeService();
        $key = 'platform/private-key';
        $service->put($key, 'initial-value');
        $secret = Secret::where('key', $key)->firstOrFail();
        $initialVersion = $secret->version;

        $service->rotate($key, 'rotated-value');

        $secret->refresh();
        $this->assertNotSame($initialVersion, $secret->version);
        $this->assertSame('rotated-value', $service->get($key));
    }

    private function makeService(): SecretVaultService
    {
        /** @var Repository $repository */
        $repository = Cache::store('array');

        return new SecretVaultService($repository);
    }
}
