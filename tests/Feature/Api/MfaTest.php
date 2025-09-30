<?php

namespace Tests\Feature\Api;

use App\Models\MfaChallenge;
use App\Models\User;
use App\Services\Mfa\TotpGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MfaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_generate_and_confirm_mfa_secret(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $setupResponse = $this->postJson('/api/v1/security/mfa/setup');
        $setupResponse->assertCreated();

        $secret = $setupResponse->json('data.secret');
        $this->assertNotEmpty($secret);

        /** @var TotpGenerator $generator */
        $generator = app(TotpGenerator::class);
        $code = $generator->getCurrentOtp($secret);

        $confirmResponse = $this->postJson('/api/v1/security/mfa/confirm', [
            'code' => $code,
        ]);

        $confirmResponse->assertOk();
        $confirmResponse->assertJsonStructure(['data' => ['recovery_codes']]);

        $this->assertTrue($user->fresh()->hasMfaEnabled());
    }

    public function test_login_requires_mfa_and_challenge_can_be_completed(): void
    {
        $password = 'StrongPassword123!';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        /** @var TotpGenerator $generator */
        $generator = app(TotpGenerator::class);
        $secret = $generator->generateSecret();
        $recoveryCodes = $user->generatePlainRecoveryCodes((int) config('security.mfa.recovery_codes', 10));
        $user->activateMfa($secret, $recoveryCodes);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
            'fcm_token' => 'unit-test-fcm-token',
        ]);

        $loginResponse->assertStatus(202);
        $loginResponse->assertJson([
            'requires_mfa' => true,
        ]);

        $challengeId = $loginResponse->json('challenge_id');
        $this->assertNotEmpty($challengeId);

        $code = $generator->getCurrentOtp($secret);
        $challengeResponse = $this->postJson('/api/v1/security/mfa/challenge', [
            'challenge_id' => $challengeId,
            'code' => $code,
        ]);

        $challengeResponse->assertOk();
        $challengeResponse->assertJsonStructure(['access_token']);

        $challenge = MfaChallenge::findOrFail($challengeId);
        $this->assertEquals('resolved', $challenge->status);
        $this->assertEquals('unit-test-fcm-token', $user->fresh()->fcm_token);
    }
}
