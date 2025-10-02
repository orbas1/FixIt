<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\DataGovernance\Dpia\StoreDpiaRecordRequest;
use App\Http\Requests\API\DataGovernance\Dpia\UpdateDpiaRecordRequest;
use App\Http\Resources\DataGovernance\DpiaRecordResource;
use App\Models\DataAsset;
use App\Models\DpiaFinding;
use App\Models\DpiaRecord;
use App\Services\DataGovernance\DpiaAutomationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DpiaRecordController extends Controller
{
    public function __construct(private readonly DpiaAutomationService $automationService)
    {
    }

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', DpiaRecord::class);

        $records = DpiaRecord::query()
            ->with(['asset', 'findings'])
            ->when(request()->filled('status'), fn ($query) => $query->where('status', request('status')))
            ->when(request()->filled('data_asset_id'), fn ($query) => $query->where('data_asset_id', request('data_asset_id')))
            ->when(request()->filled('risk_level'), fn ($query) => $query->where('risk_level', request('risk_level')))
            ->orderByDesc('created_at')
            ->paginate(request('per_page', 15));

        return DpiaRecordResource::collection($records);
    }

    public function store(StoreDpiaRecordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $asset = DataAsset::query()->findOrFail($data['data_asset_id']);

        $this->authorize('create', [DpiaRecord::class, $asset]);

        $record = $this->automationService->ensureCoverage($asset);

        if (isset($data['status'])) {
            $record->status = $data['status'];
        }

        if (isset($data['mitigation_actions'])) {
            $this->automationService->reconcileMitigations($record, $data['mitigation_actions']);
        }

        if (isset($data['residual_risks'])) {
            $record->residual_risks = $data['residual_risks'];
            $record->save();
        } else {
            $record->save();
        }

        $record->refresh()->load(['asset', 'findings']);

        return (new DpiaRecordResource($record))
            ->response()
            ->setStatusCode(201);
    }

    public function show(DpiaRecord $dpiaRecord): DpiaRecordResource
    {
        $this->authorize('view', $dpiaRecord);

        $dpiaRecord->load(['asset', 'findings']);

        return new DpiaRecordResource($dpiaRecord);
    }

    public function update(UpdateDpiaRecordRequest $request, DpiaRecord $dpiaRecord): DpiaRecordResource
    {
        $data = $request->validated();

        $dpiaRecord->fill($request->safe()->except(['findings']));
        $dpiaRecord->save();

        if (isset($data['findings'])) {
            $this->syncFindings($dpiaRecord, $data['findings']);
        }

        $dpiaRecord->refresh()->load(['asset', 'findings']);

        return new DpiaRecordResource($dpiaRecord);
    }

    public function destroy(DpiaRecord $dpiaRecord): JsonResponse
    {
        $this->authorize('delete', $dpiaRecord);

        $dpiaRecord->delete();

        return response()->json(null, 204);
    }

    private function syncFindings(DpiaRecord $record, array $findings): void
    {
        $existingIds = [];

        foreach ($findings as $findingData) {
            $finding = null;
            if (!empty($findingData['id'])) {
                $finding = $record->findings()->whereKey($findingData['id'])->first();
            }

            if (!$finding) {
                $finding = new DpiaFinding(['dpia_record_id' => $record->getKey()]);
            }

            $finding->fill([
                'category' => $findingData['category'],
                'severity' => $findingData['severity'],
                'finding' => $findingData['finding'],
                'recommendation' => $findingData['recommendation'] ?? null,
                'status' => $findingData['status'] ?? 'open',
                'due_at' => $findingData['due_at'] ?? null,
                'mitigated_at' => $findingData['mitigated_at'] ?? null,
            ]);
            $finding->save();

            $existingIds[] = $finding->getKey();
        }

        $record->findings()->whereNotIn('id', $existingIds)->delete();
    }
}
