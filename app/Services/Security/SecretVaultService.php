<?php

namespace App\Services\Security;

use App\Models\Secret;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use RuntimeException;

class SecretVaultService
{
    public function __construct(private readonly CacheRepository $cache)
    {
    }

    public function put(string $key, string|array $value, array $metadata = []): Secret
    {
        $secret = Secret::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'metadata' => $metadata,
                'rotated_at' => Carbon::now(),
                'version' => Str::uuid()->toString(),
            ]
        );

        $this->cache->forget($this->cacheKey($key));

        return $secret;
    }

    public function get(string $key, bool $failIfMissing = true): array|string|null
    {
        $ttl = Config::get('security.secrets.cache_ttl', 300);

        $secret = $this->cache->remember($this->cacheKey($key), $ttl, function () use ($key) {
            return Secret::query()->where('key', $key)->first();
        });

        if (!$secret) {
            if ($failIfMissing) {
                throw new RuntimeException("Secret [{$key}] not found in vault");
            }

            return null;
        }

        $secret->forceFill(['last_accessed_at' => Carbon::now()])->save();

        $value = $secret->value;

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            return $value;
        }

        return $value;
    }

    public function rotate(string $key, string|array $value, ?array $metadata = null): Secret
    {
        $secret = Secret::query()->where('key', $key)->first();

        if (!$secret) {
            throw new RuntimeException("Secret [{$key}] does not exist. Use put() first.");
        }

        $secret->fill([
            'value' => $value,
            'metadata' => $metadata ?? $secret->metadata,
            'version' => Str::uuid()->toString(),
            'rotated_at' => Carbon::now(),
        ])->save();

        $this->cache->forget($this->cacheKey($key));

        Log::notice('Secret rotated', [
            'key' => $key,
            'rotated_at' => $secret->rotated_at,
            'actor' => optional(Auth::user())->getAuthIdentifier(),
        ]);

        return $secret;
    }

    public function forget(string $key): void
    {
        if ($secret = Secret::query()->where('key', $key)->first()) {
            $secret->delete();
        }

        $this->cache->forget($this->cacheKey($key));
    }

    private function cacheKey(string $key): string
    {
        return "secret_vault:" . $key;
    }
}
