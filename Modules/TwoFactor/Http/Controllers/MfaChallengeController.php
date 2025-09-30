<?php

namespace Modules\TwoFactor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MfaChallenge;
use App\Services\Mfa\TotpGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MfaChallengeController extends Controller
{
    public function __construct(private readonly TotpGenerator $generator)
    {
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'challenge_id' => ['required', 'uuid'],
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        if (empty($validated['code']) && empty($validated['recovery_code'])) {
            throw ValidationException::withMessages([
                'code' => __('Provide either an authenticator code or a recovery code.'),
            ]);
        }

        /** @var MfaChallenge|null $challenge */
        $challenge = MfaChallenge::query()->with('user')->find($validated['challenge_id']);
        if (!$challenge || !$challenge->user) {
            throw ValidationException::withMessages([
                'challenge_id' => __('The requested verification challenge could not be found.'),
            ]);
        }

        if ($challenge->status === 'resolved') {
            throw ValidationException::withMessages([
                'challenge_id' => __('This verification challenge has already been completed.'),
            ]);
        }

        if ($challenge->status === 'locked') {
            throw ValidationException::withMessages([
                'challenge_id' => __('Too many invalid attempts. Please restart the sign-in process.'),
            ]);
        }

        if ($challenge->isExpired()) {
            $challenge->expire();
            throw ValidationException::withMessages([
                'challenge_id' => __('The verification challenge has expired. Please sign in again.'),
            ]);
        }

        $user = $challenge->user;
        if (!$user->hasMfaEnabled()) {
            $challenge->expire();
            throw ValidationException::withMessages([
                'challenge_id' => __('Multi-factor authentication is no longer active for this account.'),
            ]);
        }

        $maxAttempts = (int) config('security.mfa.max_attempts', 5);
        if ($challenge->hasExceededAttempts($maxAttempts)) {
            $challenge->expire();
            throw ValidationException::withMessages([
                'challenge_id' => __('Too many invalid attempts. Please restart the sign-in process.'),
            ]);
        }

        return DB::transaction(function () use ($challenge, $user, $validated) {
            $verified = false;
            $method = null;
            $window = (int) config('security.mfa.totp_window', 1);

            if (!empty($validated['code']) && $this->generator->verify($user->mfa_secret, $validated['code'], $window)) {
                $verified = true;
                $method = 'totp';
            } elseif (!empty($validated['recovery_code']) && $user->consumeRecoveryCode($validated['recovery_code'])) {
                $verified = true;
                $method = 'recovery_code';
            }

            if (!$verified) {
                $challenge->recordAttempt(false);
                throw ValidationException::withMessages([
                    'code' => __('The verification code you entered is invalid.'),
                ]);
            }

            $challenge->recordAttempt(true, $method);
            $user->markMfaVerifiedUsage();

            $metadata = $challenge->metadata ?? [];
            if (!empty($metadata['fcm_token'])) {
                $user->forceFill(['fcm_token' => $metadata['fcm_token']])->save();
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => __('Authentication challenge satisfied.'),
                'access_token' => $token,
                'success' => true,
                'recovery_codes_remaining' => $user->availableRecoveryCodesCount(),
            ]);
        });
    }
}
