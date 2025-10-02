<?php

namespace App\Console\Commands;

use App\Services\Security\ZeroTrustEvidenceService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CaptureZeroTrustEvidence extends Command
{
    protected $signature = 'security:zero-trust:evidence {--date=}';

    protected $description = 'Capture zero-trust access telemetry and persist evidence for scorecards.';

    public function handle(ZeroTrustEvidenceService $evidenceService): int
    {
        $dateOption = $this->option('date');
        $date = $dateOption ? Carbon::parse($dateOption) : Carbon::now();

        $snapshot = $evidenceService->captureSnapshot($date);
        $path = $evidenceService->persistSnapshot($snapshot);

        $this->info('Zero-trust snapshot captured.');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Interval', $snapshot['interval']['start'] . ' → ' . $snapshot['interval']['end']],
                ['Events', $snapshot['totals']['events']],
                ['Unique Users', $snapshot['totals']['unique_users']],
                ['Allow Decisions', $snapshot['totals']['decisions']['allow']],
                ['Challenges', $snapshot['totals']['decisions']['challenge']],
                ['Denies', $snapshot['totals']['decisions']['deny']],
            ]
        );

        if (!empty($snapshot['signals']['top'])) {
            $this->newLine();
            $this->info('Top signals:');
            $this->table(['Signal', 'Count', 'Percent'], array_map(function (array $signal) {
                return [$signal['signal'], $signal['count'], $signal['percentage'] . '%'];
            }, $snapshot['signals']['top']));
        }

        $this->line('Evidence stored at: ' . $path);

        return self::SUCCESS;
    }
}
