<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Security\NetworkZone\StoreNetworkZoneRequest;
use App\Http\Requests\API\Security\NetworkZone\UpdateNetworkZoneRequest;
use App\Http\Resources\Security\NetworkZoneResource;
use App\Models\NetworkZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NetworkZoneController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', NetworkZone::class);

        $zones = NetworkZone::query()
            ->when(request()->boolean('active_only'), fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->paginate(request('per_page', 15));

        return NetworkZoneResource::collection($zones);
    }

    public function store(StoreNetworkZoneRequest $request): NetworkZoneResource
    {
        $this->authorize('create', NetworkZone::class);

        $zone = NetworkZone::create($request->validated());

        return new NetworkZoneResource($zone);
    }

    public function show(NetworkZone $networkZone): NetworkZoneResource
    {
        $this->authorize('view', $networkZone);

        return new NetworkZoneResource($networkZone);
    }

    public function update(UpdateNetworkZoneRequest $request, NetworkZone $networkZone): NetworkZoneResource
    {
        $this->authorize('update', $networkZone);

        $networkZone->fill($request->validated());
        $networkZone->save();

        return new NetworkZoneResource($networkZone->refresh());
    }

    public function destroy(NetworkZone $networkZone): JsonResponse
    {
        $this->authorize('delete', $networkZone);

        $networkZone->delete();

        return response()->json(null, 204);
    }
}
