<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Feed\ServiceRequestFeedRequest;
use App\Http\Resources\Feed\ServiceRequestFeedResource;
use App\Services\Feed\ServiceRequestFeedService;
use Illuminate\Http\JsonResponse;

class FeedController extends Controller
{
    public function __construct(private readonly ServiceRequestFeedService $service)
    {
    }

    public function serviceRequests(ServiceRequestFeedRequest $request): JsonResponse
    {
        $paginator = $this->service->serviceRequests($request);

        $data = $paginator->getCollection()
            ->map(fn ($item) => (new ServiceRequestFeedResource($item))->toArray($request))
            ->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'next' => $paginator->nextPageUrl(),
                'prev' => $paginator->previousPageUrl(),
            ],
            'filters' => $this->service->appliedFilters($request),
        ]);
    }
}
