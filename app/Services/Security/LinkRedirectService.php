<?php

namespace App\Services\Security;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class LinkRedirectService
{
    private readonly string $secret;
    private readonly int $ttl;

    public function __construct(?string $secret = null, ?int $ttl = null)
    {
        $this->secret = $secret ?? Config::get('app.key');
        $this->ttl = $ttl ?? (int) Config::get('content_guard.link.ttl', 3600);
    }

    public function sign(string $url, ?Carbon $expiresAt = null): array
    {
        $expiresAt ??= Carbon::now()->addSeconds($this->ttl);
        $encoded = urlencode($url);
        $expires = $expiresAt->getTimestamp();
        $signature = $this->signature($url, $expires);

        return [
            'url' => url('/r') . '?u=' . $encoded . '&exp=' . $expires . '&sig=' . $signature,
            'expires_at' => $expiresAt,
        ];
    }

    public function verify(string $url, string $signature, int $expires): bool
    {
        if ($expires < Carbon::now()->getTimestamp()) {
            return false;
        }

        $expected = $this->signature($url, $expires);

        return hash_equals($expected, $signature);
    }

    private function signature(string $url, int $expires): string
    {
        return hash_hmac('sha256', Str::lower($url) . '|' . $expires, $this->secret);
    }
}
