<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Security\AcknowledgeSecurityIncidentRequest;
use App\Http\Requests\API\Security\CreateSecurityIncidentRequest;
use App\Http\Requests\API\Security\ResolveSecurityIncidentRequest;
use App\Http\Requests\API\Security\UpdateSecurityIncidentRequest;
use App\Http\Resources\SecurityIncidentResource;
use App\Models\SecurityIncident;
use App\Services\Security\IncidentResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;

class SecurityIncidentController extends Controller
{
    public function __construct(private readonly IncidentResponseService $incidentService)
    {
    }

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', SecurityIncident::class);

        $incidents = SecurityIncident::query()
            ->latest('detected_at')
            ->paginate(request('per_page', 15));

        return SecurityIncidentResource::collection($incidents);
    }

    public function store(CreateSecurityIncidentRequest $request): JsonResponse
    {
        $incident = $this->incidentService->report($request->validated(), $request->user());

        return (new SecurityIncidentResource($incident))
            ->response()
            ->setStatusCode(201);
    }

    public function show(SecurityIncident $incident): SecurityIncidentResource
    {
        $this->authorize('view', $incident);

        return new SecurityIncidentResource($incident);
    }

    public function update(UpdateSecurityIncidentRequest $request, SecurityIncident $incident): SecurityIncidentResource
    {
        $incident->fill(Arr::only($request->validated(), $incident->getFillable()));
        $incident->save();

        return new SecurityIncidentResource($incident->refresh());
    }

    public function acknowledge(AcknowledgeSecurityIncidentRequest $request, SecurityIncident $incident): SecurityIncidentResource
    {
        $incident = $this->incidentService->acknowledge($incident, $request->user(), $request->string('note')->toString());

        return new SecurityIncidentResource($incident);
    }

    public function resolve(ResolveSecurityIncidentRequest $request, SecurityIncident $incident): SecurityIncidentResource
    {
        $incident = $this->incidentService->resolve($incident, $request->validated(), $request->user());

        return new SecurityIncidentResource($incident);
    }

    public function close(AcknowledgeSecurityIncidentRequest $request, SecurityIncident $incident): SecurityIncidentResource
    {
        $this->authorize('resolve', $incident);

        $incident = $this->incidentService->close($incident, $request->user(), $request->string('note')->toString());

        return new SecurityIncidentResource($incident);
    }
}
