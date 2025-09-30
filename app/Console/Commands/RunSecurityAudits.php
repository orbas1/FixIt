<?php

namespace App\Console\Commands;

use App\Services\Security\AppSecAutomationService;
use Illuminate\Console\Command;

class RunSecurityAudits extends Command
{
    protected $signature = 'security:audits {--actor=system}';

    protected $description = 'Execute composer/npm audits and persist the results.';

    public function handle(AppSecAutomationService $automationService): int
    {
        $actor = (string) $this->option('actor');
        $results = $automationService->runAudits($actor);

        if ($results->isEmpty()) {
            $this->warn('No audit tools executed.');

            return self::FAILURE;
        }

        foreach ($results as $result) {
            $this->line(sprintf(
                '%s audit %s with %d issues.',
                ucfirst($result->tool),
                $result->status,
                $result->issues_found
            ));
        }

        return self::SUCCESS;
    }
}
