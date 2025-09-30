<?php

namespace Tests\Unit\Services\Security;

use App\Services\Security\ContentGuardService;
use App\Services\Security\LinkRedirectService;
use App\Support\ContentModerationResult;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ContentGuardServiceTest extends TestCase
{
    public function testDetectsBlockedTerms(): void
    {
        Config::set('content_guard.lexicons.en.block', ['forbidden']);

        $service = new ContentGuardService(new LinkRedirectService('test-secret', 60));
        $result = $service->inspect('This contains a forbidden phrase.');

        $this->assertTrue($result->isBlocked());
        $this->assertSame(ContentModerationResult::ACTION_BLOCK, $result->action());
        $this->assertNotEmpty($result->issues());
    }

    public function testRewritesLinksWithSignedRedirect(): void
    {
        $service = new ContentGuardService(new LinkRedirectService('secret', 60));
        $result = $service->inspect('Visit https://example.com for details.');

        $this->assertFalse($result->isBlocked());
        $this->assertNotSame('Visit https://example.com for details.', $result->sanitizedText());
        $this->assertNotEmpty($result->rewrittenLinks());
        $signed = $result->rewrittenLinks()[0]['signed'];
        $this->assertStringContainsString('/r?u=', $signed);
    }
}
