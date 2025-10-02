<?php

namespace App\Services\Security;

use App\Models\NetworkZone;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Services\Security\ValueObjects\ZeroTrustDecision;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils;

class ZeroTrustEvaluator
{
    private Repository $cache;
    private ?TrustedDevice $lastDevice = null;
    private ?NetworkZone $lastZone = null;

    public function __construct(?Repository $cache = null)
    {
        $this->cache = $cache ?? Cache::store();
    }

    /**
     * @param  array{ip?:string|null,user_agent?:string|null,device_identifier?:string|null,mfa_verified_at?:Carbon|null}  $context
     */
    public function evaluate(User $user, array $context = []): ZeroTrustDecision
    {
        $risk = (int) config('zero_trust.risk.baseline', 5);
        $signals = [];
        $controls = config('zero_trust.enforcement.default_controls', []);

        $ip = $context['ip'] ?? null;
        $userAgent = $context['user_agent'] ?? null;
        $deviceFingerprint = $this->fingerprintDevice($context['device_identifier'] ?? null, $userAgent);

        $trustedDevice = $this->resolveTrustedDevice($user, $deviceFingerprint, $userAgent);
        $this->lastDevice = $trustedDevice;

        if ($trustedDevice === null || !$trustedDevice->isApproved()) {
            $risk += (int) config('zero_trust.risk.new_device_penalty', 35);
            $signals[] = 'new_device_detected';
        }

        if ($trustedDevice !== null) {
            $trustedDevice->forceFill([
                'last_seen_at' => Carbon::now(),
            ])->save();
        }

        $networkZone = $this->resolveNetworkZone($ip);
        $this->lastZone = $networkZone;

        if ($networkZone !== null) {
            $controls = array_values(array_unique(array_merge($controls, $networkZone->enforced_controls ?? [])));

            if ($networkZone->type === 'blocked') {
                $signals[] = 'network_blocked';

                return $this->recordDecision(
                    $user,
                    $ip,
                    $userAgent,
                    $trustedDevice,
                    $networkZone,
                    ZeroTrustDecision::deny(100, $signals, $controls),
                    $context
                );
            }

            if ($networkZone->type !== 'trusted') {
                $risk += (int) config('zero_trust.risk.untrusted_network_penalty', 30);
                $signals[] = 'untrusted_network';
            }
        } elseif ($ip !== null) {
            $risk += (int) config('zero_trust.risk.unknown_network_penalty', 15);
            $signals[] = 'unknown_network';
        }

        if ($ip !== null) {
            $lastEvent = $user->zeroTrustAccessEvents()->latest('created_at')->first();
            if ($lastEvent !== null && $lastEvent->request_ip !== null && $lastEvent->request_ip !== $ip) {
                $minutesSinceLast = $lastEvent->created_at?->diffInMinutes(Carbon::now()) ?? PHP_INT_MAX;
                if ($minutesSinceLast <= (int) config('zero_trust.risk.impossible_travel_threshold_minutes', 120)) {
                    $risk += (int) config('zero_trust.risk.impossible_travel_penalty', 40);
                    $signals[] = 'impossible_travel_detected';
                }
            }
        }

        $mfaVerifiedAt = $context['mfa_verified_at'] ?? $user->mfa_last_used_at;
        if ($mfaVerifiedAt instanceof Carbon) {
            $minutesSinceMfa = $mfaVerifiedAt->diffInMinutes(Carbon::now());
            if ($minutesSinceMfa > (int) config('zero_trust.risk.stale_mfa_minutes', 720)) {
                $risk += (int) config('zero_trust.risk.stale_mfa_penalty', 25);
                $signals[] = 'stale_mfa_session';
            }
        } elseif ($user->hasMfaEnabled()) {
            $risk += (int) config('zero_trust.risk.stale_mfa_penalty', 25);
            $signals[] = 'mfa_not_verified_this_session';
        }

        $requireMfaRoles = array_map('strtolower', config('zero_trust.enforcement.require_mfa_roles', []));
        $userRoles = $user->getRoleNames()->map(static fn (string $role) => strtolower($role));
        if ($userRoles->intersect($requireMfaRoles)->isNotEmpty() && !$user->hasMfaEnabled()) {
            $signals[] = 'mfa_required_for_role';

            return $this->recordDecision(
                $user,
                $ip,
                $userAgent,
                $trustedDevice,
                $networkZone,
                ZeroTrustDecision::deny(95, $signals, $controls),
                $context
            );
        }

        $decision = $this->scoreToDecision($risk, $signals, $controls);

        return $this->recordDecision(
            $user,
            $ip,
            $userAgent,
            $trustedDevice,
            $networkZone,
            $decision,
            $context
        );
    }

    private function resolveNetworkZone(?string $ip): ?NetworkZone
    {
        if ($ip === null) {
            return null;
        }

        /** @var array<int, array{zone: NetworkZone, ranges: array<int, string>}> $zones */
        $zones = $this->cache->remember('zero_trust.network_zones', 300, static function () {
            return NetworkZone::query()
                ->where('is_active', true)
                ->get(['id', 'slug', 'name', 'type', 'risk_level', 'ip_ranges', 'enforced_controls'])
                ->map(function (NetworkZone $zone) {
                    return [
                        'id' => $zone->id,
                        'zone' => $zone,
                        'ranges' => $zone->normalizedIpRanges(),
                    ];
                })
                ->all();
        });

        foreach ($zones as $record) {
            foreach ($record['ranges'] as $range) {
                if (IpUtils::checkIp($ip, $range)) {
                    return $record['zone'];
                }
            }
        }

        return null;
    }

    private function resolveTrustedDevice(User $user, string $fingerprint, ?string $userAgent): ?TrustedDevice
    {
        $device = $user->trustedDevices()->where('device_identifier', $fingerprint)->first();

        if ($device === null) {
            if (empty($fingerprint)) {
                return null;
            }

            $device = $user->trustedDevices()->create([
                'device_identifier' => $fingerprint,
                'display_name' => $this->guessDisplayName($userAgent),
                'platform' => $this->guessPlatform($userAgent),
                'trust_level' => 'pending',
                'signals' => [
                    'user_agent' => $userAgent,
                ],
            ]);
        }

        $ttlDays = (int) config('zero_trust.enforcement.trusted_device_ttl_days', 90);
        if ($device->approved_at !== null && $device->approved_at->diffInDays(Carbon::now()) > $ttlDays) {
            $device->forceFill([
                'trust_level' => 'expired',
                'revoked_at' => Carbon::now(),
            ])->save();
        }

        return $device;
    }

    public function lastEvaluatedDevice(): ?TrustedDevice
    {
        return $this->lastDevice;
    }

    public function lastEvaluatedZone(): ?NetworkZone
    {
        return $this->lastZone;
    }

    private function guessDisplayName(?string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return null;
        }

        if (Str::contains($userAgent, 'iPhone')) {
            return 'iPhone';
        }
        if (Str::contains($userAgent, 'iPad')) {
            return 'iPad';
        }
        if (Str::contains($userAgent, 'Android')) {
            return 'Android Device';
        }
        if (Str::contains($userAgent, 'Mac OS X')) {
            return 'Mac';
        }
        if (Str::contains($userAgent, 'Windows')) {
            return 'Windows';
        }

        return Str::limit($userAgent, 40);
    }

    private function guessPlatform(?string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return null;
        }

        if (Str::contains($userAgent, 'Android')) {
            return 'android';
        }
        if (Str::contains($userAgent, 'iPhone') || Str::contains($userAgent, 'iPad')) {
            return 'ios';
        }
        if (Str::contains($userAgent, 'Mac OS X')) {
            return 'macos';
        }
        if (Str::contains($userAgent, 'Windows')) {
            return 'windows';
        }
        if (Str::contains($userAgent, 'Linux')) {
            return 'linux';
        }

        return 'unknown';
    }

    private function fingerprintDevice(?string $identifier, ?string $userAgent): string
    {
        $candidate = trim((string) ($identifier ?? ''));
        if ($candidate === '') {
            $candidate = $userAgent ? Str::substr(hash('sha256', $userAgent), 0, 32) : 'anonymous';
        }

        return hash('sha256', $candidate);
    }

    private function scoreToDecision(int $risk, array $signals, array $controls): ZeroTrustDecision
    {
        $denyThreshold = (int) config('zero_trust.risk.deny_threshold', 85);
        $challengeThreshold = (int) config('zero_trust.risk.challenge_threshold', 55);

        if ($risk >= $denyThreshold) {
            return ZeroTrustDecision::deny($risk, $signals, $controls);
        }

        if ($risk >= $challengeThreshold) {
            return ZeroTrustDecision::challenge($risk, $signals, $controls);
        }

        return ZeroTrustDecision::allow($risk, $signals, $controls);
    }

    private function recordDecision(
        User $user,
        ?string $ip,
        ?string $userAgent,
        ?TrustedDevice $device,
        ?NetworkZone $zone,
        ZeroTrustDecision $decision,
        array $context
    ): ZeroTrustDecision {
        $event = $user->zeroTrustAccessEvents()->create([
            'trusted_device_id' => $device?->getKey(),
            'network_zone_id' => $zone?->getKey(),
            'request_ip' => $ip,
            'user_agent' => $userAgent,
            'decision' => $decision->decision(),
            'risk_score' => $decision->riskScore(),
            'signals' => $decision->signals(),
            'policy_version' => config('zero_trust.policy_version'),
            'mfa_verified_at' => $context['mfa_verified_at'] ?? null,
            'enforced_controls' => $decision->enforcedControls(),
        ]);

        $this->cache->put(
            sprintf('zero_trust.last_event.%d', $user->getKey()),
            $event->only(['id', 'decision', 'risk_score', 'created_at']),
            now()->addMinutes(30)
        );

        return $decision;
    }
}
