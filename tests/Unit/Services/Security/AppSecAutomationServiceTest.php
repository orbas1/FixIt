<?php

namespace Tests\Unit\Services\Security;

use App\Models\SecurityAuditReport;
use App\Services\Security\AppSecAutomationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AppSecAutomationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_results_from_audit_tools(): void
    {
        $runner = function (string $command): array {
            if (str_contains($command, 'composer')) {
                return [
                    'success' => true,
                    'output' => json_encode(['advisories_count' => 0, 'status' => 'ok'], JSON_THROW_ON_ERROR),
                    'error' => '',
                ];
            }

            return [
                'success' => false,
                'output' => '',
                'error' => 'npm audit not available',
            ];
        };

        $service = new AppSecAutomationService($runner);
        $results = $service->runAudits('qa-tester');

        $this->assertCount(2, $results);

        $composer = SecurityAuditReport::where('tool', 'composer')->first();
        $this->assertNotNull($composer);
        $this->assertSame('passed', $composer->status);
        $this->assertSame(0, $composer->issues_found);
        $this->assertSame('qa-tester', $composer->executed_by);
        $this->assertInstanceOf(Carbon::class, $composer->executed_at);

        $npm = SecurityAuditReport::where('tool', 'npm')->first();
        $this->assertNotNull($npm);
        $this->assertSame('failed', $npm->status);
        $this->assertSame('npm audit not available', $npm->summary['error']);
    }
}
