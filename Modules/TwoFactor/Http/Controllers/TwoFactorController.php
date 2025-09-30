<?php

namespace Modules\TwoFactor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Mfa\TotpGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function __construct(private readonly TotpGenerator $generator)
    {
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'enabled' => $user->hasMfaEnabled(),
                'enabled_at' => optional($user->mfa_enabled_at)->toIso8601String(),
                'last_used_at' => optional($user->mfa_last_used_at)->toIso8601String(),
                'pending' => !empty($user->mfa_pending_secret),
                'methods' => $user->hasMfaEnabled() ? ['totp', 'recovery_code'] : [],
                'recovery_codes_remaining' => $user->availableRecoveryCodesCount(),
                'enforcement_level' => $user->mfa_enforcement_level,
            ],
        ]);
    }

    public function setup(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasMfaEnabled() && empty($user->mfa_pending_secret)) {
            throw ValidationException::withMessages([
                'secret' => __('Two-factor authentication is already enabled.'),
            ]);
        }

        $secret = $user->mfa_pending_secret ?? $this->generator->generateSecret();
        if (empty($user->mfa_pending_secret)) {
            $user->createPendingMfaSecret($secret);
        }

        $label = $user->email ?? (string) ($user->phone ?? $user->name ?? $user->getKey());
        $issuer = config('app.name');

        return response()->json([
            'message' => __('Two-factor secret generated successfully.'),
            'data' => [
                'secret' => $secret,
                'provisioning_uri' => $this->generator->getProvisioningUri($label, $issuer, $secret),
                'methods' => ['totp'],
            ],
        ], 201);
    }

    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'regenerate_recovery_codes' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();
        $secret = $user->mfa_pending_secret ?? $user->mfa_secret;

        if (!$secret) {
            throw ValidationException::withMessages([
                'code' => __('Generate a secret before confirming multi-factor authentication.'),
            ]);
        }

        $window = (int) config('security.mfa.totp_window', 1);
        if (!$this->generator->verify($secret, $validated['code'], $window)) {
            throw ValidationException::withMessages([
                'code' => __('The provided authentication code is invalid or expired.'),
            ]);
        }

        $response = [
            'message' => __('Two-factor authentication confirmed.'),
            'data' => [
                'enabled' => true,
            ],
        ];

        $regenerate = (bool) ($validated['regenerate_recovery_codes'] ?? false);

        if (!$user->hasMfaEnabled()) {
            $recoveryCodes = $user->generatePlainRecoveryCodes((int) config('security.mfa.recovery_codes', 10));
            $user->activateMfa($secret, $recoveryCodes);
            $response['data']['recovery_codes'] = $recoveryCodes;
        } elseif (!empty($user->mfa_pending_secret)) {
            $user->rotateMfaSecret($secret);
            if ($regenerate) {
                $response['data']['recovery_codes'] = $user->regenerateRecoveryCodes((int) config('security.mfa.recovery_codes', 10));
            }
        } elseif ($regenerate) {
            $response['data']['recovery_codes'] = $user->regenerateRecoveryCodes((int) config('security.mfa.recovery_codes', 10));
        }

        $user->markMfaVerifiedUsage();

        return response()->json($response);
    }

    public function regenerateRecoveryCodes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        if (!$user->hasMfaEnabled()) {
            throw ValidationException::withMessages([
                'code' => __('Enable multi-factor authentication before requesting recovery codes.'),
            ]);
        }

        if (empty($validated['code']) && empty($validated['password'])) {
            throw ValidationException::withMessages([
                'code' => __('Provide either your password or a valid authentication code.'),
            ]);
        }

        if (!empty($validated['password']) && !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('The provided password is incorrect.'),
            ]);
        }

        if (!empty($validated['code']) && !$this->generator->verify($user->mfa_secret, $validated['code'], (int) config('security.mfa.totp_window', 1))) {
            throw ValidationException::withMessages([
                'code' => __('The provided authentication code is invalid or expired.'),
            ]);
        }

        $codes = $user->regenerateRecoveryCodes((int) config('security.mfa.recovery_codes', 10));

        if (!empty($validated['code'])) {
            $user->markMfaVerifiedUsage();
        }

        return response()->json([
            'message' => __('Recovery codes regenerated.'),
            'data' => [
                'recovery_codes' => $codes,
            ],
        ]);
    }

    public function disable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        if (!$user->hasMfaEnabled()) {
            return response()->json([
                'message' => __('Two-factor authentication is already disabled.'),
            ]);
        }

        $verified = false;
        $window = (int) config('security.mfa.totp_window', 1);

        if (!empty($validated['password']) && Hash::check($validated['password'], $user->password)) {
            $verified = true;
        }

        if (!$verified && !empty($validated['code']) && $this->generator->verify($user->mfa_secret, $validated['code'], $window)) {
            $verified = true;
        }

        if (!$verified && !empty($validated['recovery_code']) && $user->consumeRecoveryCode($validated['recovery_code'])) {
            $verified = true;
        }

        if (!$verified) {
            throw ValidationException::withMessages([
                'code' => __('Unable to verify your credentials. Provide a valid code, password, or recovery code.'),
            ]);
        }

        $user->clearMfa();

        return response()->json([
            'message' => __('Two-factor authentication disabled successfully.'),
        ]);
    }
}
