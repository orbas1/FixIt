<?php

namespace App\Services\Feed;

use App\Enums\BidStatusEnum;
use App\Http\Requests\API\Feed\ServiceRequestFeedRequest;
use App\Models\ServiceRequest;
use App\Services\Geo\H3IndexService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceRequestFeedService
{
    public const CACHE_TAG = 'feed:service_requests';
    public const CACHE_VERSION_PREFIX = 'feed:service_requests:version:';

    private bool $cacheSupportsTags;

    public function __construct(private readonly H3IndexService $h3IndexService)
    {
        $this->cacheSupportsTags = method_exists(Cache::getStore(), 'tags');
    }

    public function serviceRequests(ServiceRequestFeedRequest $request): LengthAwarePaginator
    {
        $buckets = $this->resolveBuckets($request);
        $cacheKey = $this->cacheKey($request, $buckets);
        $ttl = (int) config('services.h3.cache_ttl', 60);

        $callback = fn () => $this->buildPaginator($request, $buckets);

        if ($this->cacheSupportsTags) {
            return Cache::tags($this->cacheTags($buckets))->remember($cacheKey, $ttl, $callback);
        }

        return Cache::remember($cacheKey, $ttl, $callback);
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
            'h3_indexes' => $this->resolveBuckets($request),
        ];
    }

    public function flushCache(): void
    {
        if ($this->cacheSupportsTags) {
            Cache::tags([self::CACHE_TAG])->flush();
        }

        // Reset the global version so non-tag stores also invalidate.
        Cache::forget(self::CACHE_VERSION_PREFIX . 'global');
    }

    public function invalidateBuckets(array $buckets): void
    {
        $buckets = array_values(array_unique($buckets));

        if ($this->cacheSupportsTags) {
            foreach ($buckets as $bucket) {
                Cache::tags($this->cacheTags([$bucket]))->flush();
            }
        }

        foreach ($buckets as $bucket) {
            Cache::forget(self::CACHE_VERSION_PREFIX . $bucket);
        }
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

    protected function cacheKey(ServiceRequestFeedRequest $request, array $buckets): string
    {
        $versions = [];

        foreach ($buckets as $bucket) {
            $versions[$bucket] = $this->bucketVersion($bucket);
        }

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
            'buckets' => $buckets,
            'bucket_versions' => $versions,
        ];

        return 'feed:service_requests:' . md5(json_encode($payload));
    }

    protected function resolveBuckets(ServiceRequestFeedRequest $request): array
    {
        if (! $request->hasLocation()) {
            return ['global'];
        }

        $latitude = (float) $request->latitude();
        $longitude = (float) $request->longitude();
        $radius = max(1.0, (float) ($request->radius() ?? 25.0));
        $resolution = (int) config('services.h3.feed_resolution', 8);

        $indexes = $this->h3IndexService->indexesForRadius($latitude, $longitude, $radius, $resolution);

        sort($indexes);

        return array_map(static fn ($value) => (string) $value, $indexes);
    }

    protected function bucketVersion(string $bucket): string
    {
        $key = self::CACHE_VERSION_PREFIX . $bucket;

        return Cache::rememberForever($key, static fn () => (string) Str::uuid());
    }

    protected function cacheTags(array $buckets): array
    {
        $tags = [self::CACHE_TAG];

        foreach ($buckets as $bucket) {
            $tags[] = self::CACHE_TAG . ':bucket:' . $bucket;
        }

        return $tags;
    }

    protected function buildPaginator(ServiceRequestFeedRequest $request, array $buckets): LengthAwarePaginator
    {
        $query = ServiceRequest::query()
            ->select('service_requests.*')
            ->with([
                'user.media',
                'service:id,title,price,user_id',
                'zones:id,name',
                'media' => function ($builder) {
                    $builder->select([
                        'id',
                        'model_id',
                        'model_type',
                        'collection_name',
                        'name',
                        'file_name',
                        'mime_type',
                        'disk',
                        'size',
                        'manipulations',
                        'custom_properties',
                        'generated_conversions',
                        'responsive_images',
                        'created_at',
                    ])->where('collection_name', 'attachments');
                },
                'bids' => function ($builder) {
                    $builder->latest('created_at')
                        ->select(['id', 'service_request_id', 'provider_id', 'amount', 'status', 'created_at'])
                        ->with(['provider:id,name']);
                },
            ])
            ->where('status', $request->status())
            ->withCount([
                'bids as bids_total' => function ($builder) {
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

        $numericBuckets = array_values(array_filter($buckets, static fn ($bucket) => is_numeric($bucket)));

        if ($numericBuckets) {
            $query->whereIn('h3_index', $numericBuckets);
        }

        if ($request->hasLocation()) {
            $lat = $request->latitude();
            $lng = $request->longitude();
            $radius = $request->radius() ?? 25.0;

            $haversine = '6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))';
            $bindings = [$lat, $lng, $lat];

            $query->selectRaw($haversine . ' as distance_km', $bindings);

            if ($request->radius() !== null) {
                $latRange = $radius / 111.0;
                $lngRange = $radius / (111.0 * max(cos(deg2rad($lat)), 0.1));

                $query->whereBetween('latitude', [$lat - $latRange, $lat + $latRange])
                    ->whereBetween('longitude', [$lng - $lngRange, $lng + $lngRange]);

                if (DB::connection()->getDriverName() === 'sqlite') {
                    $query->whereRaw($haversine . ' <= ?', array_merge($bindings, [$request->radius()]));
                } else {
                    $query->having('distance_km', '<=', $request->radius());
                }
            }
        }

        if ($user = $request->user() ?? Auth::user()) {
            $query->where(function ($builder) use ($user) {
                $builder->whereNull('provider_id')
                    ->orWhere('provider_id', '!=', $user->id);
            });
        }

        $this->applyOrdering($query, $request);

        $paginator = $query->paginate($request->perPage());
        $paginator->appends($request->query());

        return $paginator;
    }
}
