<?php

namespace App\Services\Security\ValueObjects;

class ZeroTrustDecision
{
    public function __construct(
        private readonly string $decision,
        private readonly int $riskScore,
        private readonly array $signals = [],
        private readonly array $enforcedControls = []
    ) {
    }

    public static function allow(int $riskScore, array $signals = [], array $controls = []): self
    {
        return new self('allow', $riskScore, $signals, $controls);
    }

    public static function challenge(int $riskScore, array $signals = [], array $controls = []): self
    {
        return new self('challenge', $riskScore, $signals, $controls);
    }

    public static function deny(int $riskScore, array $signals = [], array $controls = []): self
    {
        return new self('deny', $riskScore, $signals, $controls);
    }

    public function decision(): string
    {
        return $this->decision;
    }

    public function riskScore(): int
    {
        return $this->riskScore;
    }

    public function signals(): array
    {
        return $this->signals;
    }

    public function enforcedControls(): array
    {
        return $this->enforcedControls;
    }

    public function requiresChallenge(): bool
    {
        return $this->decision === 'challenge';
    }

    public function isDenied(): bool
    {
        return $this->decision === 'deny';
    }
}
