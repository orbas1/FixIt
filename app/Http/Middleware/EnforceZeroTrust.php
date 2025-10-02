<?php

namespace App\Http\Middleware;

use App\Services\Security\ZeroTrustEvaluator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceZeroTrust
{
    public function __construct(private readonly ZeroTrustEvaluator $zeroTrustEvaluator)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $context = [
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'device_identifier' => $request->header('X-Device-Id'),
            'mfa_verified_at' => $user->mfa_last_used_at,
        ];

        $decision = $this->zeroTrustEvaluator->evaluate($user, $context);

        if ($decision->isDenied()) {
            return response()->json([
                'type' => 'https://fixit.security/problems/zero-trust-denied',
                'title' => __('Access blocked by conditional access'),
                'status' => 403,
                'detail' => __('Your session was blocked by enterprise security policies.'),
                'zero_trust' => [
                    'decision' => $decision->decision(),
                    'risk_score' => $decision->riskScore(),
                    'signals' => $decision->signals(),
                ],
            ], 403, ['Content-Type' => 'application/problem+json']);
        }

        if ($decision->requiresChallenge()) {
            return response()->json([
                'type' => 'https://fixit.security/problems/zero-trust-challenge',
                'title' => __('Additional verification required'),
                'status' => 423,
                'detail' => __('We need to re-verify your identity. Please re-authenticate.'),
                'zero_trust' => [
                    'decision' => $decision->decision(),
                    'risk_score' => $decision->riskScore(),
                    'signals' => $decision->signals(),
                ],
            ], 423, ['Content-Type' => 'application/problem+json']);
        }

        $request->attributes->set('zero_trust_decision', $decision);
        $request->attributes->set('zero_trust_device', $this->zeroTrustEvaluator->lastEvaluatedDevice());

        return $next($request);
    }
}
