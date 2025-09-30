<?php

namespace Tests\Concerns;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait EnsuresInstallationState
{
    /**
     * Ensure all prerequisite installation artifacts exist for guarded middleware checks.
     */
    protected function prepareInstallationArtifacts(): void
    {
        $filesystem = new Filesystem();
        $basePath = realpath(__DIR__ . '/../../') ?: getcwd();
        $publicPath = $basePath . DIRECTORY_SEPARATOR . 'public';

        $filesystem->ensureDirectoryExists($publicPath);

        $this->createEnvironmentFileIfMissing($filesystem, $basePath);
        $this->createInstallationManifestIfMissing($filesystem, $publicPath);
        $this->createLicenseManifestIfMissing($filesystem, $publicPath);
        $this->createTestingDatabaseIfMissing($filesystem, $basePath);
    }

    protected function ensureInstallationState(): void
    {
        $this->ensureTestingDatabaseExists();
        $this->ensureInstallationManifestExists();
        $this->ensureLicenseManifestExists();
    }

    private function createEnvironmentFileIfMissing(Filesystem $filesystem, string $basePath): void
    {
        $envPath = $basePath . DIRECTORY_SEPARATOR . '.env';

        if ($filesystem->exists($envPath)) {
            return;
        }

        $appKey = 'base64:' . base64_encode(random_bytes(32));

        $environment = <<<ENV
APP_NAME="FixIt Test Suite"
APP_ENV=testing
APP_KEY={$appKey}
APP_DEBUG=true
APP_URL=http://localhost
ENV;

        $filesystem->put($envPath, $environment . PHP_EOL);
    }

    private function createInstallationManifestIfMissing(Filesystem $filesystem, string $publicPath): void
    {
        $installationPath = $publicPath . DIRECTORY_SEPARATOR . 'installation.json';

        if ($filesystem->exists($installationPath)) {
            return;
        }

        $manifest = [
            'installed' => true,
            'completed_at' => Carbon::now()->toIso8601String(),
            'environment' => 'testing',
            'version' => 'testing',
        ];

        $filesystem->put($installationPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function createLicenseManifestIfMissing(Filesystem $filesystem, string $publicPath): void
    {
        $licensePath = $publicPath . DIRECTORY_SEPARATOR . '_log.dic.xml';

        if ($filesystem->exists($licensePath) && trim($filesystem->get($licensePath)) !== '') {
            return;
        }

        $filesystem->put($licensePath, base64_encode('http://localhost'));
    }

    private function createTestingDatabaseIfMissing(Filesystem $filesystem, string $basePath): void
    {
        $database = $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?? 'database/testing.sqlite';

        if (empty($database) || $database === ':memory:') {
            return;
        }

        if (! Str::startsWith($database, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\\\/', $database)) {
            $database = $basePath . DIRECTORY_SEPARATOR . $database;
        }

        $filesystem->ensureDirectoryExists(dirname($database));

        if (! $filesystem->exists($database)) {
            $filesystem->put($database, '');
        }
    }

    private function ensureTestingDatabaseExists(): void
    {
        $database = config('database.connections.sqlite.database');

        if (empty($database) || $database === ':memory:') {
            return;
        }

        if (! Str::startsWith($database, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\\\/', $database)) {
            $database = base_path($database);
        }

        File::ensureDirectoryExists(dirname($database));

        if (! File::exists($database)) {
            File::put($database, '');
        }
    }

    private function ensureInstallationManifestExists(): void
    {
        $installationPath = public_path(config('config.installation', 'installation.json'));

        File::ensureDirectoryExists(dirname($installationPath));

        if (File::exists($installationPath)) {
            return;
        }

        $manifest = [
            'installed' => true,
            'completed_at' => Carbon::now()->toIso8601String(),
            'environment' => app()->environment(),
            'version' => config('app.version', 'testing'),
        ];

        File::put($installationPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function ensureLicenseManifestExists(): void
    {
        $licensePath = public_path('_log.dic.xml');

        File::ensureDirectoryExists(dirname($licensePath));

        $expectedHost = config('app.url', 'http://localhost');
        if (! parse_url($expectedHost, PHP_URL_SCHEME)) {
            $expectedHost = 'http://' . ltrim($expectedHost, '/');
        }

        $encodedHost = base64_encode($expectedHost);

        if (! File::exists($licensePath) || trim(File::get($licensePath)) === '') {
            File::put($licensePath, $encodedHost);
            return;
        }

        $currentHost = trim(File::get($licensePath));
        if ($currentHost !== $encodedHost) {
            File::put($licensePath, $encodedHost);
        }
    }
}
