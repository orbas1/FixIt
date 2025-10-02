<?php

namespace App\Console\Commands;

use App\Models\DataAsset;
use App\Services\DataGovernance\DataGovernanceRegistry;
use Illuminate\Console\Command;

class GovernanceDataDoctorCommand extends Command
{
    protected $signature = 'governance:data-doctor {--format=table : Output format (table or json)}';

    protected $description = 'Evaluate data governance compliance for registered data assets.';

    public function handle(DataGovernanceRegistry $registry): int
    {
        $format = $this->option('format');

        $assets = DataAsset::with(['residencyPolicies.zone', 'dpiaRecords'])->orderBy('name')->get();
        $evaluations = $assets->map(function (DataAsset $asset) use ($registry) {
            $compliance = $registry->evaluateCompliance($asset);

            return [
                'asset' => $asset->key,
                'name' => $asset->name,
                'classification' => $asset->classification,
                'requires_dpia' => $asset->requires_dpia,
                'dpia_records' => $asset->dpiaRecords->count(),
                'status' => $compliance['status'],
                'issues' => $compliance['issues'],
                'evaluated_at' => $compliance['evaluated_at'],
            ];
        });

        if ($format === 'json') {
            $this->output->writeln(json_encode([
                'generated_at' => now()->toIso8601String(),
                'asset_count' => $evaluations->count(),
                'non_compliant' => $evaluations->where('status', 'non_compliant')->count(),
                'attention_required' => $evaluations->where('status', 'attention_required')->count(),
                'items' => $evaluations->values(),
            ], JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        if ($evaluations->isEmpty()) {
            $this->info('No data assets registered.');
            return self::SUCCESS;
        }

        $this->table([
            'Asset Key',
            'Name',
            'Classification',
            'Requires DPIA',
            'DPIA Records',
            'Status',
            'Issues',
        ], $evaluations->map(function (array $item) {
            return [
                $item['asset'],
                $item['name'],
                $item['classification'],
                $item['requires_dpia'] ? 'yes' : 'no',
                $item['dpia_records'],
                $item['status'],
                empty($item['issues']) ? 'none' : implode('; ', $item['issues']),
            ];
        })->toArray());

        return self::SUCCESS;
    }
}
