<?php

namespace App\Services\Security;

use App\Models\SecurityAuditReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AppSecAutomationService
{
    /**
     * @param (callable(string): array{success: bool, output: string, error: string})|null $commandRunner
     */
    public function __construct(private readonly $commandRunner = null)
    {
    }

    public function runAudits(?string $actor = null): Collection
    {
        $results = collect([
            $this->runComposerAudit($actor),
            $this->runNpmAudit($actor),
        ])->filter();

        if ($results->isEmpty()) {
            Log::warning('Security automation executed but no tools returned results.');
        }

        return $results;
    }

    private function runComposerAudit(?string $actor): ?SecurityAuditReport
    {
        $lockFile = base_path('composer.lock');
        if (!file_exists($lockFile)) {
            return null;
        }

        $result = $this->runCommand('composer audit --format=json');

        if (!$result['success']) {
            return $this->persistFailure('composer', $result['error'] ?: $result['output'], $actor);
        }

        $data = json_decode($result['output'], true, 512, JSON_THROW_ON_ERROR);
        $issues = $data['advisories_count'] ?? 0;

        return $this->persistSuccess('composer', $issues, $data, $actor);
    }

    private function runNpmAudit(?string $actor): ?SecurityAuditReport
    {
        $lockFile = base_path('package-lock.json');
        if (!file_exists($lockFile)) {
            return null;
        }

        $result = $this->runCommand('npm audit --json');

        if (!$result['success']) {
            return $this->persistFailure('npm', $result['error'] ?: $result['output'], $actor);
        }

        $data = json_decode($result['output'], true, 512, JSON_THROW_ON_ERROR);
        $issues = data_get($data, 'metadata.vulnerabilities.total', 0);

        return $this->persistSuccess('npm', $issues, $data, $actor);
    }

    private function runCommand(string $command): array
    {
        if (is_callable($this->commandRunner)) {
            return ($this->commandRunner)($command);
        }

        $process = \Symfony\Component\Process\Process::fromShellCommandline($command, base_path());
        $process->run();

        return [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput(),
            'error' => $process->getErrorOutput(),
        ];
    }

    private function persistSuccess(string $tool, int $issues, array $summary, ?string $actor): SecurityAuditReport
    {
        return SecurityAuditReport::create([
            'tool' => $tool,
            'status' => $issues === 0 ? 'passed' : 'issues_found',
            'issues_found' => $issues,
            'summary' => $summary,
            'executed_by' => $actor,
            'executed_at' => Carbon::now(),
        ]);
    }

    private function persistFailure(string $tool, string $message, ?string $actor): SecurityAuditReport
    {
        Log::error('Security audit failed', [
            'tool' => $tool,
            'error' => $message,
        ]);

        return SecurityAuditReport::create([
            'tool' => $tool,
            'status' => 'failed',
            'issues_found' => 0,
            'summary' => ['error' => $message],
            'executed_by' => $actor,
            'executed_at' => Carbon::now(),
        ]);
    }
}
