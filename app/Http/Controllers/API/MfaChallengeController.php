<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Security\Mfa\VerifyMfaChallengeRequest;
use App\Models\MfaChallenge;
use App\Services\Mfa\TotpGenerator;
use App\Services\Security\ZeroTrustEvaluator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class MfaChallengeController extends Controller
{
    public function __construct(
        private readonly TotpGenerator $totpGenerator,
        private readonly ZeroTrustEvaluator $zeroTrustEvaluator
    ) {
    }

    public function verify(VerifyMfaChallengeRequest $request): JsonResponse
    {
        $challenge = MfaChallenge::with('user')->findOrFail($request->string('challenge_id')->toString());

        if ($challenge->status === 'locked') {
            return $this->problem('https://fixit.security/problems/mfa-locked', __('Challenge locked'), 423, __('Too many failed attempts. Request a new verification challenge.'));
        }

        if ($challenge->isExpired()) {
            $challenge->expire();

            return $this->problem('https://fixit.security/problems/mfa-expired', __('Challenge expired'), 410, __('The verification window expired. Please start again.'));
        }

        $user = $challenge->user;
        if (!$user->hasMfaEnabled()) {
            return $this->problem(
                'https://fixit.security/problems/mfa-not-enabled',
                __('Multi-factor authentication not enabled'),
                409,
                __('The user has no active authenticator. Contact support to restore access.')
            );
        }

        $verified = false;
        $method = null;

        if ($request->filled('code')) {
            $method = 'totp';
            $verified = $this->totpGenerator->verify($user->mfa_secret, $request->string('code')->toString());
        } elseif ($request->filled('recovery_code')) {
            $method = 'recovery_code';
            $verified = $user->consumeRecoveryCode($request->string('recovery_code')->toString());
        }

        if (!$verified) {
            $challenge->recordAttempt(false, $method);

            return $this->problem(
                'https://fixit.security/problems/mfa-invalid',
                __('Verification failed'),
                422,
                __('The provided code was invalid. Check the code and try again.')
            );
        }

        $challenge->recordAttempt(true, $method);
        $user->markMfaVerifiedUsage();

        $metadata = $challenge->metadata ?? [];
        $context = [
            'ip' => request()->ip(),
            'user_agent' => (string) request()->userAgent(),
            'device_identifier' => request()->header('X-Device-Id') ?? Arr::get($metadata, 'device_identifier'),
            'mfa_verified_at' => Carbon::now(),
        ];

        $decision = $this->zeroTrustEvaluator->evaluate($user, $context);

        if ($decision->isDenied()) {
            return $this->problem(
                'https://fixit.security/problems/zero-trust-denied',
                __('Access blocked by conditional access'),
                403,
                __('Your verification succeeded but the risk engine denied access. Contact support.'),
                [
                    'zero_trust' => [
                        'decision' => $decision->decision(),
                        'risk_score' => $decision->riskScore(),
                        'signals' => $decision->signals(),
                    ],
                ]
            );
        }

        if ($decision->requiresChallenge()) {
            return $this->problem(
                'https://fixit.security/problems/zero-trust-secondary-challenge',
                __('Additional approval required'),
                423,
                __('We still need to confirm your identity through a secondary path. Reach the security desk.'),
                [
                    'zero_trust' => [
                        'decision' => $decision->decision(),
                        'risk_score' => $decision->riskScore(),
                        'signals' => $decision->signals(),
                    ],
                ]
            );
        }

        if (!empty($metadata['fcm_token'])) {
            $user->forceFill(['fcm_token' => $metadata['fcm_token']])->save();
        }

        $device = $this->zeroTrustEvaluator->lastEvaluatedDevice();
        if ($device !== null && !$device->isApproved()) {
            $device->approve('mfa_challenge');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'recovery_codes_remaining' => $user->availableRecoveryCodesCount(),
            'zero_trust' => [
                'decision' => $decision->decision(),
                'risk_score' => $decision->riskScore(),
                'signals' => $decision->signals(),
            ],
        ]);
    }

    private function problem(string $type, string $title, int $status, string $detail, array $additional = []): JsonResponse
    {
        return response()->json(
            array_merge([
                'type' => $type,
                'title' => $title,
                'status' => $status,
                'detail' => $detail,
            ], $additional),
            $status,
            ['Content-Type' => 'application/problem+json']
        );
    }
}
