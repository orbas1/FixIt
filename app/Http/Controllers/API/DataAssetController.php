<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\DataGovernance\DataAsset\StoreDataAssetRequest;
use App\Http\Requests\API\DataGovernance\DataAsset\UpdateDataAssetRequest;
use App\Http\Requests\API\DataGovernance\Dpia\EnsureDpiaCoverageRequest;
use App\Http\Resources\DataGovernance\DataAssetResource;
use App\Models\DataAsset;
use App\Services\DataGovernance\DataGovernanceRegistry;
use App\Services\DataGovernance\DpiaAutomationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DataAssetController extends Controller
{
    public function __construct(
        private readonly DataGovernanceRegistry $registry,
        private readonly DpiaAutomationService $automationService
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', DataAsset::class);

        $assets = DataAsset::query()
            ->with(['domain', 'steward', 'residencyPolicies.zone'])
            ->when(request()->filled('classification'), fn ($query) => $query->where('classification', request('classification')))
            ->when(request()->has('requires_dpia'), fn ($query) => $query->where('requires_dpia', request()->boolean('requires_dpia')))
            ->orderBy('name')
            ->paginate(request('per_page', 15));

        $assets->getCollection()->transform(function (DataAsset $asset) {
            $asset->compliance = $this->registry->evaluateCompliance($asset);
            return $asset;
        });

        return DataAssetResource::collection($assets);
    }

    public function store(StoreDataAssetRequest $request): JsonResponse
    {
        $asset = $this->registry->register($request->validated(), $request->user());
        $asset->compliance = $this->registry->evaluateCompliance($asset);

        return (new DataAssetResource($asset))
            ->response()
            ->setStatusCode(201);
    }

    public function show(DataAsset $dataAsset): DataAssetResource
    {
        $this->authorize('view', $dataAsset);

        $dataAsset->loadMissing(['domain', 'steward', 'residencyPolicies.zone', 'dpiaRecords.findings']);
        $dataAsset->compliance = $this->registry->evaluateCompliance($dataAsset);

        return new DataAssetResource($dataAsset);
    }

    public function update(UpdateDataAssetRequest $request, DataAsset $dataAsset): DataAssetResource
    {
        $asset = $this->registry->update($dataAsset, $request->validated(), $request->user());
        $asset->compliance = $this->registry->evaluateCompliance($asset);

        return new DataAssetResource($asset);
    }

    public function destroy(DataAsset $dataAsset): JsonResponse
    {
        $this->authorize('delete', $dataAsset);

        $dataAsset->delete();

        return response()->json(null, 204);
    }

    public function ensureDpia(EnsureDpiaCoverageRequest $request, DataAsset $dataAsset): DataAssetResource
    {
        $payload = $request->validated();
        $this->automationService->ensureCoverage($dataAsset);

        if (!empty($payload['mitigation_actions'])) {
            $latestRecord = $dataAsset->dpiaRecords()->latest('created_at')->first();
            if ($latestRecord) {
                $this->automationService->reconcileMitigations($latestRecord, $payload['mitigation_actions']);
            }
        }

        if (!empty($payload['residual_risks'])) {
            $latestRecord = $dataAsset->dpiaRecords()->latest('created_at')->first();
            if ($latestRecord) {
                $latestRecord->residual_risks = $payload['residual_risks'];
                $latestRecord->save();
            }
        }

        $dataAsset->loadMissing(['domain', 'steward', 'residencyPolicies.zone', 'dpiaRecords.findings']);
        $dataAsset->compliance = $this->registry->evaluateCompliance($dataAsset);

        return new DataAssetResource($dataAsset);
    }
}
