<?php

namespace App\Services\Media;

use InvalidArgumentException;

class ImgproxyUrlBuilder
{
    public function __construct(
        private readonly ?string $endpoint,
        private readonly ?string $key,
        private readonly ?string $salt,
        private readonly bool $enabled,
        private readonly string $defaultFormat,
        private readonly int $defaultQuality
    ) {
    }

    public function make(string $sourceUrl, array $options = []): string
    {
        if (! $this->enabled || empty($this->endpoint)) {
            return $sourceUrl;
        }

        $resize = $options['resize'] ?? 'fill';
        $width = (int) ($options['width'] ?? 0);
        $height = (int) ($options['height'] ?? 0);

        if ($width <= 0 || $height <= 0) {
            throw new InvalidArgumentException('Imgproxy variants require positive width and height.');
        }

        $quality = (int) ($options['quality'] ?? $this->defaultQuality);
        $gravity = $options['gravity'] ?? null;
        $format = $options['format'] ?? $this->defaultFormat;
        $dpr = max(1, (int) ($options['dpr'] ?? 1));

        $operations = [sprintf('resize:%s:%d:%d', $resize, $width, $height)];

        if ($gravity) {
            $operations[] = 'gravity:' . $gravity;
        }

        if ($dpr > 1) {
            $operations[] = 'dpr:' . $dpr;
        }

        $operations[] = 'quality:' . $quality;

        $encodedUrl = rtrim(strtr(base64_encode($sourceUrl), '+/', '-_'), '=');
        $path = implode('/', $operations) . '/plain/' . $encodedUrl;

        if ($format) {
            $path .= '@' . $format;
        }

        $signature = $this->signature('/' . ltrim($path, '/'));

        return rtrim($this->endpoint, '/') . '/' . $signature . '/' . ltrim($path, '/');
    }

    private function signature(string $path): string
    {
        if (empty($this->key) || empty($this->salt)) {
            return 'insecure';
        }

        $decodedKey = base64_decode($this->key, true);
        $decodedSalt = base64_decode($this->salt, true);

        if ($decodedKey === false || $decodedSalt === false) {
            throw new InvalidArgumentException('Imgproxy key and salt must be base64-encoded.');
        }

        $hash = hash_hmac('sha256', $decodedSalt . $path, $decodedKey, true);

        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }
}
