<?php

namespace App\Services\Security;

use App\Models\FileScan;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Process\Process;

class FileScanService
{
    private const CACHE_PREFIX = 'file_scan_sha256_';
    private const MAX_FILE_SIZE_BYTES = 25 * 1024 * 1024; // 25MB
    private const DISALLOWED_EXTENSIONS = [
        'exe', 'msi', 'bat', 'cmd', 'com', 'scr', 'js', 'jar', 'vbs', 'ps1', 'apk', 'ipa',
    ];

    private const SUSPICIOUS_MIME_TYPES = [
        'application/x-msdownload',
        'application/x-dosexec',
        'application/java-archive',
        'application/x-ms-installer',
        'application/x-sh',
        'application/x-bat',
    ];

    public function scan(Media $media): FileScan
    {
        $path = $media->getPath();
        if ($path === false || ! file_exists($path)) {
            throw new FileNotFoundException("Unable to locate media file for scan [ID: {$media->id}].");
        }

        $media->setCustomProperty('scan_status', 'pending');
        $media->save();

        $report = [
            'checks' => [],
        ];
        $verdict = 'clean';
        $engine = 'heuristic';
        $sha256 = hash_file('sha256', $path) ?: null;
        $size = filesize($path) ?: null;
        $mime = $media->mime_type ?: $this->guessMimeType($path);

        $report['checks'][] = $this->validateFileSize($size);
        $report['checks'][] = $this->validateExtension($media->file_name);
        $report['checks'][] = $this->validateMimeType($mime, $media->file_name);

        foreach ($report['checks'] as $check) {
            if ($check['status'] === 'suspicious') {
                $verdict = 'suspicious';
            }
            if ($check['status'] === 'malicious') {
                $verdict = 'malicious';
                break;
            }
        }

        if ($verdict !== 'malicious') {
            $clamResult = $this->runClamAv($path);
            if ($clamResult !== null) {
                $engine = 'clamav';
                $report['clamav'] = $clamResult;
                if ($clamResult['status'] === 'malicious') {
                    $verdict = 'malicious';
                } elseif ($clamResult['status'] === 'suspicious' && $verdict !== 'malicious') {
                    $verdict = 'suspicious';
                }
            }
        }

        $scan = FileScan::updateOrCreate(
            ['media_id' => $media->id],
            [
                'engine' => $engine,
                'verdict' => $verdict,
                'report' => $report,
                'sha256' => $sha256,
                'file_size' => $size,
                'mime_type' => $mime,
            ],
        );

        $media->setCustomProperty('scan_status', $verdict);
        $media->setCustomProperty('scan_report', $report);
        if ($verdict !== 'clean') {
            $media->setCustomProperty('quarantined', true);
        }
        $media->save();

        if ($sha256) {
            Cache::put(self::CACHE_PREFIX . $sha256, [
                'media_id' => $media->id,
                'verdict' => $verdict,
                'checked_at' => now()->toISOString(),
            ], now()->addDay());
        }

        return $scan;
    }

    private function validateFileSize(?int $size): array
    {
        if ($size === null) {
            return [
                'check' => 'size',
                'status' => 'suspicious',
                'detail' => 'File size unavailable; enforcing cautious handling.',
            ];
        }

        if ($size > self::MAX_FILE_SIZE_BYTES) {
            return [
                'check' => 'size',
                'status' => 'malicious',
                'detail' => 'File exceeds maximum allowed size for pre-publish scanning.',
            ];
        }

        return [
            'check' => 'size',
            'status' => 'clean',
            'detail' => 'Within allowed file size limits.',
        ];
    }

    private function validateExtension(?string $fileName): array
    {
        $extension = $fileName ? strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION)) : null;
        if ($extension && in_array($extension, self::DISALLOWED_EXTENSIONS, true)) {
            return [
                'check' => 'extension',
                'status' => 'malicious',
                'detail' => "File extension [{$extension}] is not permitted.",
            ];
        }

        return [
            'check' => 'extension',
            'status' => 'clean',
            'detail' => 'Extension allowed.',
        ];
    }

    private function validateMimeType(?string $mime, ?string $fileName): array
    {
        if ($mime === null) {
            return [
                'check' => 'mime',
                'status' => 'suspicious',
                'detail' => 'Unable to resolve MIME type from upload.',
            ];
        }

        if (in_array($mime, self::SUSPICIOUS_MIME_TYPES, true)) {
            return [
                'check' => 'mime',
                'status' => 'malicious',
                'detail' => "MIME type [{$mime}] is disallowed.",
            ];
        }

        if ($fileName) {
            $extension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
            $validExtensions = Arr::flatten(MimeTypes::getDefault()->getExtensions($mime));
            if (! empty($validExtensions) && $extension && ! in_array($extension, $validExtensions, true)) {
                return [
                    'check' => 'mime',
                    'status' => 'suspicious',
                    'detail' => "Extension [{$extension}] does not match MIME type [{$mime}].",
                ];
            }
        }

        return [
            'check' => 'mime',
            'status' => 'clean',
            'detail' => 'MIME type accepted.',
        ];
    }

    private function runClamAv(string $path): ?array
    {
        $binary = $this->resolveClamAvBinary();
        if ($binary === null) {
            return null;
        }

        try {
            $process = new Process([$binary, '--no-summary', $path]);
            $process->setTimeout(60);
            $process->run();

            if ($process->getExitCode() === 0) {
                return [
                    'status' => 'clean',
                    'detail' => 'ClamAV scan completed successfully.',
                ];
            }

            if ($process->getExitCode() === 1) {
                return [
                    'status' => 'malicious',
                    'detail' => trim($process->getOutput()),
                ];
            }

            return [
                'status' => 'suspicious',
                'detail' => trim($process->getErrorOutput()) ?: 'ClamAV returned an indeterminate status.',
            ];
        } catch (\Throwable $exception) {
            Log::warning('ClamAV invocation failed', [
                'error' => $exception->getMessage(),
            ]);

            return [
                'status' => 'suspicious',
                'detail' => 'ClamAV execution failed: ' . $exception->getMessage(),
            ];
        }
    }

    private function resolveClamAvBinary(): ?string
    {
        $candidates = ['clamscan', '/usr/bin/clamscan', '/usr/local/bin/clamscan'];
        foreach ($candidates as $candidate) {
            if (@is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function guessMimeType(string $path): ?string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (! $finfo) {
            return null;
        }

        $mime = finfo_file($finfo, $path) ?: null;
        finfo_close($finfo);

        return $mime;
    }
}
