<?php

namespace App\Support;

class ContentModerationResult
{
    public const ACTION_ALLOW = 'allow';
    public const ACTION_REVIEW = 'review';
    public const ACTION_BLOCK = 'block';

    public function __construct(
        private readonly string $action,
        private readonly string $sanitizedText,
        private readonly array $issues = [],
        private readonly array $links = []
    ) {
    }

    public function action(): string
    {
        return $this->action;
    }

    public function sanitizedText(): string
    {
        return $this->sanitizedText;
    }

    public function issues(): array
    {
        return $this->issues;
    }

    public function rewrittenLinks(): array
    {
        return $this->links;
    }

    public function isBlocked(): bool
    {
        return $this->action === self::ACTION_BLOCK;
    }

    public function shouldEscalate(): bool
    {
        return in_array($this->action, [self::ACTION_REVIEW, self::ACTION_BLOCK], true);
    }
}
