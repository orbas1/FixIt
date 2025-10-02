<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\DataGovernance\DataResidencyZone\StoreDataResidencyZoneRequest;
use App\Http\Requests\API\DataGovernance\DataResidencyZone\UpdateDataResidencyZoneRequest;
use App\Http\Resources\DataGovernance\DataResidencyZoneResource;
use App\Models\DataResidencyZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DataResidencyZoneController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', DataResidencyZone::class);

        $zones = DataResidencyZone::query()
            ->when(request()->filled('region'), fn ($query) => $query->where('region', request('region')))
            ->when(request()->has('is_active'), fn ($query) => $query->where('is_active', request()->boolean('is_active')))
            ->orderBy('name')
            ->paginate(request('per_page', 15));

        return DataResidencyZoneResource::collection($zones);
    }

    public function store(StoreDataResidencyZoneRequest $request): JsonResponse
    {
        $zone = DataResidencyZone::create($request->validated());

        return (new DataResidencyZoneResource($zone))
            ->response()
            ->setStatusCode(201);
    }

    public function show(DataResidencyZone $dataResidencyZone): DataResidencyZoneResource
    {
        $this->authorize('view', $dataResidencyZone);

        return new DataResidencyZoneResource($dataResidencyZone);
    }

    public function update(UpdateDataResidencyZoneRequest $request, DataResidencyZone $dataResidencyZone): DataResidencyZoneResource
    {
        $dataResidencyZone->fill($request->validated());
        $dataResidencyZone->save();

        return new DataResidencyZoneResource($dataResidencyZone->refresh());
    }

    public function destroy(DataResidencyZone $dataResidencyZone): JsonResponse
    {
        $this->authorize('delete', $dataResidencyZone);

        $dataResidencyZone->delete();

        return response()->json(null, 204);
    }
}
