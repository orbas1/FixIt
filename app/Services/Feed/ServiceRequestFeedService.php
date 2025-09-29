<?php

namespace App\Services\Feed;

use App\Enums\BidStatusEnum;
use App\Http\Requests\API\Feed\ServiceRequestFeedRequest;
use App\Models\ServiceRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServiceRequestFeedService
{
    public function serviceRequests(ServiceRequestFeedRequest $request): LengthAwarePaginator
    {
        $cacheKey = $this->cacheKey($request);
        $ttl = (int) config('services.h3.cache_ttl', 60);

        return Cache::remember($cacheKey, $ttl, function () use ($request) {
            $query = ServiceRequest::query()
                ->select('service_requests.*')
                ->with([
                    'user.media',
                    'service:id,title,price,user_id',
                    'zones:id,name',
                    'bids' => function ($builder) {
                        $builder->latest('created_at')
                            ->select(['id', 'service_request_id', 'provider_id', 'amount', 'status', 'created_at'])
                            ->with(['provider:id,name']);
                    },
                ])
                ->where('status', $request->status())
                ->withCount([
                    'bids as bids_total' => function ($builder) {
                        // include soft-deleted rows only when available
                        $builder->whereNull('bids.deleted_at');
                    },
                    'bids as bids_pending' => function ($builder) {
                        $builder->whereNull('bids.deleted_at')->where('status', BidStatusEnum::REQUESTED);
                    },
                    'bids as bids_accepted' => function ($builder) {
                        $builder->whereNull('bids.deleted_at')->where('status', BidStatusEnum::ACCEPTED);
                    },
                ]);

            if ($search = $request->input('search')) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            if ($request->categories()) {
                foreach ($request->categories() as $categoryId) {
                    $query->whereJsonContains('category_ids', (int) $categoryId);
                }
            }

            if ($request->zoneIds()) {
                $query->whereHas('zones', function ($builder) use ($request) {
                    $builder->whereIn('zones.id', $request->zoneIds());
                });
            }

            if ($request->budgetMin() !== null) {
                $query->where(DB::raw('COALESCE(final_price, initial_price)'), '>=', $request->budgetMin());
            }

            if ($request->budgetMax() !== null) {
                $query->where(DB::raw('COALESCE(final_price, initial_price)'), '<=', $request->budgetMax());
            }

            if ($request->hasLocation()) {
                $lat = $request->latitude();
                $lng = $request->longitude();
                $haversine = '6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))';
                $query->selectRaw($haversine . ' as distance_km', [$lat, $lng, $lat]);

                if ($request->radius() !== null) {
                    $query->having('distance_km', '<=', $request->radius());
                }
            }

            $this->applyOrdering($query, $request);

            $paginator = $query->paginate($request->perPage());
            $paginator->appends($request->query());

            return $paginator;
        });
    }

    public function appliedFilters(ServiceRequestFeedRequest $request): array
    {
        return [
            'status' => $request->status(),
            'categories' => $request->categories(),
            'zone_ids' => $request->zoneIds(),
            'budget_min' => $request->budgetMin(),
            'budget_max' => $request->budgetMax(),
            'ordering' => $request->ordering(),
            'radius' => $request->radius(),
            'has_location' => $request->hasLocation(),
        ];
    }

    protected function applyOrdering($query, ServiceRequestFeedRequest $request): void
    {
        switch ($request->ordering()) {
            case 'distance':
                if ($request->hasLocation()) {
                    $query->orderBy('distance_km');
                } else {
                    $query->latest('created_at');
                }
                break;
            case 'expiring':
                $query->orderBy('booking_date');
                break;
            case 'budget_high':
                $query->orderByDesc(DB::raw('COALESCE(final_price, initial_price)'));
                break;
            case 'budget_low':
                $query->orderBy(DB::raw('COALESCE(final_price, initial_price)'));
                break;
            default:
                $query->latest('created_at');
                break;
        }
    }

    protected function cacheKey(ServiceRequestFeedRequest $request): string
    {
        $payload = [
            'page' => $request->input('page', 1),
            'per_page' => $request->perPage(),
            'status' => $request->status(),
            'categories' => $request->categories(),
            'zone_ids' => $request->zoneIds(),
            'budget_min' => $request->budgetMin(),
            'budget_max' => $request->budgetMax(),
            'ordering' => $request->ordering(),
            'latitude' => $request->latitude(),
            'longitude' => $request->longitude(),
            'radius' => $request->radius(),
            'search' => $request->input('search'),
        ];

        return 'feed:service_requests:' . md5(json_encode($payload));
    }
}
