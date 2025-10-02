<?php

namespace App\Services\DataGovernance;

use App\Models\DataAsset;
use App\Models\DataResidencyPolicy;
use App\Models\DataResidencyZone;
use App\Models\User;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DataGovernanceRegistry
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly CacheRepository $cache
    ) {
    }

    public function register(array $payload, User $actor): DataAsset
    {
        return $this->db->transaction(function () use ($payload, $actor) {
            $asset = new DataAsset();
            $asset->fill(Arr::except($payload, ['residency_policies']));
            $asset->created_by = $actor->getKey();
            $asset->updated_by = $actor->getKey();
            $asset->save();

            $this->syncPolicies($asset, $payload['residency_policies'] ?? []);

            $this->cache->forget($this->cacheKey($asset->key));

            return $asset->fresh(['domain', 'steward', 'residencyPolicies.zone']);
        });
    }

    public function update(DataAsset $asset, array $payload, User $actor): DataAsset
    {
        return $this->db->transaction(function () use ($asset, $payload, $actor) {
            $asset->fill(Arr::except($payload, ['residency_policies']));
            $asset->updated_by = $actor->getKey();
            $asset->save();

            if (array_key_exists('residency_policies', $payload)) {
                $this->syncPolicies($asset, $payload['residency_policies']);
            }

            $this->cache->forget($this->cacheKey($asset->key));

            return $asset->fresh(['domain', 'steward', 'residencyPolicies.zone']);
        });
    }

    public function evaluateCompliance(DataAsset $asset): array
    {
        return $this->cache->remember($this->cacheKey($asset->key), now()->addMinutes(15), function () use ($asset) {
            $policies = $asset->residencyPolicies()->with('zone')->get();

            $status = 'compliant';
            $issues = [];

            if ($asset->requires_dpia && $asset->dpiaRecords()->whereIn('status', ['draft', 'mitigation_required'])->doesntExist()) {
                $status = 'attention_required';
                $issues[] = 'Asset requires DPIA but no active record is pending or approved.';
            }

            $allowedRegions = collect(config('data_governance.residency_policies.regions'))
                ->flatMap(fn ($countries) => $countries)
                ->unique()
                ->values();

            foreach ($policies as $policy) {
                $zone = $policy->zone;

                if (!$zone->is_active) {
                    $status = 'attention_required';
                    $issues[] = sprintf('Zone %s is inactive for storage role %s.', $zone->code, $policy->storage_role);
                }

                $unknownCountries = collect($zone->country_codes)
                    ->filter(fn ($code) => !$allowedRegions->contains($code));

                if ($unknownCountries->isNotEmpty()) {
                    $status = 'attention_required';
                    $issues[] = sprintf(
                        'Zone %s includes countries without approved residency policy: %s.',
                        $zone->code,
                        $unknownCountries->implode(', ')
                    );
                }

                if ($policy->cross_border_allowed && empty($policy->transfer_safeguards)) {
                    $status = 'non_compliant';
                    $issues[] = sprintf('Cross-border transfer in %s lacks safeguards.', $zone->code);
                }
            }

            $nextReview = $asset->next_review_at ? Carbon::parse($asset->next_review_at) : null;
            if ($nextReview && $nextReview->isPast()) {
                $status = 'attention_required';
                $issues[] = 'Asset review date has passed.';
            }

            return [
                'status' => $status,
                'issues' => $issues,
                'evaluated_at' => now()->toIso8601ZuluString(),
            ];
        });
    }

    public function syncPolicies(DataAsset $asset, array $policies): void
    {
        $existingPolicyIds = [];

        foreach ($policies as $policyData) {
            $zone = $this->resolveZone($policyData['zone_code'] ?? null);
            if (!$zone) {
                Log::warning('Skipping policy sync for missing zone code', ['asset' => $asset->key, 'payload' => $policyData]);
                continue;
            }

            /** @var DataResidencyPolicy $policy */
            $policy = $asset->residencyPolicies()
                ->where('data_residency_zone_id', $zone->getKey())
                ->where('storage_role', $policyData['storage_role'])
                ->first();

            if (!$policy) {
                $policy = new DataResidencyPolicy([
                    'data_asset_id' => $asset->getKey(),
                    'data_residency_zone_id' => $zone->getKey(),
                    'storage_role' => $policyData['storage_role'],
                ]);
            }

            $policy->fill([
                'lawful_basis' => $policyData['lawful_basis'],
                'encryption_profile' => $policyData['encryption_profile'],
                'data_controller' => $policyData['data_controller'],
                'cross_border_allowed' => (bool) ($policyData['cross_border_allowed'] ?? false),
                'transfer_safeguards' => $policyData['transfer_safeguards'] ?? null,
                'audit_controls' => $policyData['audit_controls'] ?? null,
            ]);
            $policy->save();

            $existingPolicyIds[] = $policy->getKey();
        }

        $asset->residencyPolicies()
            ->whereNotIn('id', $existingPolicyIds)
            ->delete();
    }

    private function resolveZone(?string $code): ?DataResidencyZone
    {
        if (!$code) {
            return null;
        }

        return DataResidencyZone::query()->where('code', $code)->first();
    }

    private function cacheKey(string $assetKey): string
    {
        return sprintf('data_governance.asset_compliance.%s', $assetKey);
    }
}
