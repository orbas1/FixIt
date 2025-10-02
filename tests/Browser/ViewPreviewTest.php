<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\DuskTestCase;

#[Group('view-previews')]
class ViewPreviewTest extends DuskTestCase
{
    #[DataProvider('viewProvider')]
    public function test_view_can_be_rendered(array $definition): void
    {
        $this->browse(function (Browser $browser) use ($definition) {
            $width = $definition['width'] ?? 1280;
            $height = $definition['height'] ?? 720;

            $browser->resize($width, $height);

            $browser->visitRoute('testing.view-preview', [
                'view' => $definition['view'],
            ] + $this->buildQueryData($definition))
                ->assertPresent('body');

            if (filled($definition['wait_for'] ?? null)) {
                $browser->waitFor($definition['wait_for']);
            }

            $browser->screenshot($this->screenshotName($definition['view'], $width, $height));
        });
    }

    /**
     * @return array<int, array{view: string, data?: array<string, mixed>, width?: int, height?: int, wait_for?: string|null}>
     */
    public static function viewProvider(): array
    {
        return array_values(array_filter(require base_path('tests/Browser/views.php'), function ($definition) {
            return filled($definition['view'] ?? null);
        }));
    }

    /**
     * @param  array{data?: array<string, mixed>|null}  $definition
     * @return array<string, mixed>
     */
    private function buildQueryData(array $definition): array
    {
        $payload = $definition['data'] ?? [];

        if (empty($payload)) {
            return [];
        }

        return [
            'data' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}',
        ];
    }

    private function screenshotName(string $view, int $width, int $height): string
    {
        $safe = str_replace('.', '-', $view);

        return sprintf('%s-%dx%d', $safe, $width, $height);
    }
}
