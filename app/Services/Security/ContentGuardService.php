<?php

namespace App\Services\Security;

use App\Models\ModerationFlag;
use App\Support\ContentModerationResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class ContentGuardService
{
    public function __construct(private readonly LinkRedirectService $redirector)
    {
    }

    public function inspect(string $text, array $options = []): ContentModerationResult
    {
        $locale = Str::lower((string) ($options['locale'] ?? app()->getLocale() ?? 'en'));
        $lexicon = Config::get("content_guard.lexicons.{$locale}", Config::get('content_guard.lexicons.en'));
        $patterns = Config::get('content_guard.patterns');

        $issues = [];
        $sanitized = $text;
        $action = ContentModerationResult::ACTION_ALLOW;

        foreach ((array) ($lexicon['block'] ?? []) as $term) {
            if ($this->containsPattern($text, $term)) {
                $issues[] = ['type' => 'lexicon', 'severity' => 'high', 'term' => $term];
                $action = ContentModerationResult::ACTION_BLOCK;
            }
        }

        if ($action !== ContentModerationResult::ACTION_BLOCK) {
            foreach ((array) ($lexicon['review'] ?? []) as $term) {
                if ($this->containsPattern($text, $term)) {
                    $issues[] = ['type' => 'lexicon', 'severity' => 'medium', 'term' => $term];
                    $action = $action === ContentModerationResult::ACTION_ALLOW
                        ? ContentModerationResult::ACTION_REVIEW
                        : $action;
                }
            }
        }

        foreach (['email' => 'contact_email', 'phone' => 'contact_phone'] as $patternKey => $reason) {
            if ($pattern = Arr::get($patterns, $patternKey)) {
                if (preg_match($pattern, $text)) {
                    $issues[] = ['type' => $reason, 'severity' => 'medium'];
                    $action = $action === ContentModerationResult::ACTION_ALLOW
                        ? ContentModerationResult::ACTION_REVIEW
                        : $action;
                }
            }
        }

        $rewrittenLinks = [];
        if ($pattern = Arr::get($patterns, 'url')) {
            $sanitized = preg_replace_callback($pattern, function (array $matches) use (&$issues, &$rewrittenLinks) {
                $url = $matches[0];
                $parsed = parse_url($url);
                if (! $parsed || empty($parsed['host'])) {
                    return $url;
                }

                if (filter_var($parsed['host'], FILTER_VALIDATE_IP)) {
                    $issues[] = ['type' => 'link_ip', 'severity' => 'high', 'url' => $url];

                    return $url;
                }

                $signed = $this->redirector->sign($url);
                $rewrittenLinks[] = ['original' => $url, 'signed' => $signed['url']];

                return $signed['url'];
            }, $sanitized) ?? $sanitized;
        }

        if (collect($issues)->firstWhere('severity', 'high')) {
            $action = ContentModerationResult::ACTION_BLOCK;
        }

        return new ContentModerationResult($action, $sanitized, $issues, $rewrittenLinks);
    }

    public function flag(Model $model, ContentModerationResult $result, array $context = []): ModerationFlag
    {
        return $model->moderationFlags()->create([
            'category' => Arr::get($context, 'category', 'content'),
            'severity' => collect($result->issues())
                ->pluck('severity')
                ->sortDesc()
                ->first() ?? 'medium',
            'action' => $result->action(),
            'reasons' => $result->issues(),
            'snapshot' => [
                'text' => $result->sanitizedText(),
                'context' => $context,
            ],
            'detected_at' => Carbon::now(),
        ]);
    }

    public function assertSafe(string $text, array $options = []): string
    {
        $result = $this->inspect($text, $options);

        if ($result->isBlocked()) {
            throw new \DomainException('Submitted content violates marketplace policies.');
        }

        return $result->sanitizedText();
    }

    protected function containsPattern(string $text, string $pattern): bool
    {
        $pattern = trim($pattern);
        if ($pattern === '') {
            return false;
        }

        $regex = '/' . str_replace('/', '\/', $pattern) . '/i';

        return (bool) preg_match($regex, $text);
    }
}
