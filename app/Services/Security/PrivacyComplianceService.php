<?php

namespace App\Services\Security;

use App\Jobs\Security\ProcessDataDeletionRequest;
use App\Models\PrivacyConsent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class PrivacyComplianceService
{
    public function updatePreferences(User $user, array $preferences): void
    {
        DB::transaction(function () use ($user, $preferences) {
            foreach ($preferences as $preference => $granted) {
                PrivacyConsent::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'preference' => $preference,
                    ],
                    [
                        'granted' => (bool) $granted,
                        'granted_at' => Carbon::now(),
                        'expires_at' => $this->expiresAt($preference),
                    ]
                );
            }
        });
    }

    public function getPreferences(User $user): array
    {
        $defaults = config('security.privacy.default_preferences', []);

        $preferences = $user->privacyConsents
            ->keyBy('preference')
            ->map(fn (PrivacyConsent $consent) => [
                'granted' => $consent->granted,
                'granted_at' => $consent->granted_at,
                'expires_at' => $consent->expires_at,
            ])->toArray();

        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $preferences)) {
                $preferences[$key] = [
                    'granted' => (bool) $value,
                    'granted_at' => null,
                    'expires_at' => null,
                ];
            }
        }

        ksort($preferences);

        return $preferences;
    }

    public function requestDataExport(User $user): string
    {
        $token = Str::uuid()->toString();
        Log::info('Privacy data export requested', ['user_id' => $user->id, 'token' => $token]);

        return $token;
    }

    public function requestDataDeletion(User $user): string
    {
        $token = Str::uuid()->toString();
        ProcessDataDeletionRequest::dispatch($user->id, $token);

        return $token;
    }

    public function enforceRetention(User $user, string $preference): void
    {
        $consent = $user->privacyConsents()->where('preference', $preference)->first();

        if (!$consent) {
            throw new RuntimeException("Consent [{$preference}] not found for user #{$user->id}");
        }

        if ($consent->granted && $consent->expires_at && $consent->expires_at->isPast()) {
            Log::notice('Consent expired, scheduling data deletion', [
                'user_id' => $user->id,
                'preference' => $preference,
            ]);

            $this->requestDataDeletion($user);
        }
    }

    private function expiresAt(string $preference): ?Carbon
    {
        return match ($preference) {
            'marketing' => Carbon::now()->addYear(),
            'analytics' => Carbon::now()->addMonths(18),
            default => null,
        };
    }
}
