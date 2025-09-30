<?php

namespace App\Models\Concerns;

use App\Models\MfaChallenge;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait HasMultiFactorAuthentication
{
    public function mfaChallenges(): HasMany
    {
        return $this->hasMany(MfaChallenge::class);
    }

    public function hasMfaEnabled(): bool
    {
        return !empty($this->mfa_secret) && !empty($this->mfa_enabled_at);
    }

    public function createPendingMfaSecret(string $secret): void
    {
        $this->forceFill([
            'mfa_pending_secret' => $secret,
            'mfa_pending_secret_created_at' => now(),
        ])->save();
    }

    public function activateMfa(string $secret, array $plainRecoveryCodes): void
    {
        $this->forceFill([
            'mfa_secret' => $secret,
            'mfa_pending_secret' => null,
            'mfa_pending_secret_created_at' => null,
            'mfa_enabled_at' => now(),
            'mfa_last_used_at' => null,
            'mfa_recovery_codes' => $this->hashRecoveryCodes($plainRecoveryCodes),
            'mfa_recovery_codes_generated_at' => now(),
        ])->save();
    }

    public function generatePlainRecoveryCodes(int $count): array
    {
        return $this->makeRecoveryCodes($count);
    }

    public function rotateMfaSecret(string $secret): void
    {
        $this->forceFill([
            'mfa_secret' => $secret,
            'mfa_pending_secret' => null,
            'mfa_pending_secret_created_at' => null,
            'mfa_enabled_at' => $this->mfa_enabled_at ?? now(),
        ])->save();
    }

    public function regenerateRecoveryCodes(int $count): array
    {
        $codes = $this->generatePlainRecoveryCodes($count);
        $this->forceFill([
            'mfa_recovery_codes' => $this->hashRecoveryCodes($codes),
            'mfa_recovery_codes_generated_at' => now(),
        ])->save();

        return $codes;
    }

    public function availableRecoveryCodesCount(): int
    {
        return collect($this->mfa_recovery_codes ?? [])->filter(function ($code) {
            return empty($code['used_at']);
        })->count();
    }

    public function consumeRecoveryCode(string $plainCode): bool
    {
        $codes = $this->mfa_recovery_codes ?? [];
        $updated = false;
        foreach ($codes as $index => $record) {
            if (!empty($record['used_at'])) {
                continue;
            }

            if (Hash::check($plainCode, $record['code'])) {
                $codes[$index]['used_at'] = now()->toIso8601String();
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->forceFill(['mfa_recovery_codes' => $codes, 'mfa_last_used_at' => now()])->save();
        }

        return $updated;
    }

    public function clearMfa(): void
    {
        $this->forceFill([
            'mfa_secret' => null,
            'mfa_pending_secret' => null,
            'mfa_pending_secret_created_at' => null,
            'mfa_enabled_at' => null,
            'mfa_last_used_at' => null,
            'mfa_recovery_codes' => null,
            'mfa_recovery_codes_generated_at' => null,
        ])->save();
    }

    public function startMfaChallenge(array $metadata = []): MfaChallenge
    {
        $this->mfaChallenges()
            ->where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $challenge = $this->mfaChallenges()->create([
            'expires_at' => CarbonImmutable::now()->addSeconds(config('security.mfa.challenge_ttl')),
            'metadata' => Arr::except($metadata, ['ip', 'user_agent']),
            'request_ip' => $metadata['ip'] ?? null,
            'user_agent' => $metadata['user_agent'] ?? null,
        ]);

        return $challenge;
    }

    public function markMfaVerifiedUsage(): void
    {
        $this->forceFill([
            'mfa_last_used_at' => now(),
        ])->save();
    }

    protected function makeRecoveryCodes(int $count): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        }

        return $codes;
    }

    protected function hashRecoveryCodes(array $codes): array
    {
        return array_map(function (string $code) {
            return [
                'code' => Hash::make($code),
                'used_at' => null,
            ];
        }, $codes);
    }
}
