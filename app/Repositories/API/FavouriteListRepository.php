<?php

namespace App\Repositories\API;

use Exception;
use App\Enums\FavouriteListEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Resources\FavouriteListResource;
use App\Models\FavouriteList;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Symfony\Component\HttpFoundation\Response;

class FavouriteListRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'service.title' => 'like',
        'provider.name' => 'like',
    ];

    public function model()
    {
        return FavouriteList::class;
    }

    /**
     * Generate cache key based on request
     */
    private function getCacheKey($request): string
    {
        return 'favourite_list_' . auth()->id() . '_' . ($request->type ?? 'all') . '_' . ($request->search ?? 'none');
    }

    /**
    * Clear all possible favourite list cache keys for current user
    */
    private function clearFavouriteListCache(): void
    {
        $userId = auth()->id();
        $types = ['all', FavouriteListEnum::PROVIDER, FavouriteListEnum::SERVICE];
        $searches = ['none'];

        foreach ($types as $type) {
            foreach ($searches as $search) {
                Cache::forget("favourite_list_{$userId}_{$type}_{$search}");
            }
        }
    }

    public function index($request)
    {
        $cacheKey = $this->getCacheKey($request);
        
        return Cache::remember($cacheKey, 60, function () use ($request) {
            $query = $this->model->where('consumer_id', auth()->id());
    
            if ($request->has('type')) {
                switch ($request->type) {
                    case FavouriteListEnum::PROVIDER:
                        $query->whereNotNull('provider_id');
                        break;
                    case FavouriteListEnum::SERVICE:
                        $query->whereNotNull('service_id');
                        break;
                }
            }
    
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->whereHas('provider', function ($providerQuery) use ($searchTerm) {
                        $providerQuery->where('name', 'like', "%$searchTerm%");
                    });
    
                    $q->orWhereHas('service', function ($serviceQuery) use ($searchTerm) {
                        $serviceQuery->where('title', 'like', "%$searchTerm%");
                    });
                });
            }

            $results = $query->with([
                'provider.media',
                'service.media',
                'service.categories',
            ])->get();

            return FavouriteListResource::collection($results);
        });
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $consumerId = Helpers::getCurrentUserId();
            switch ($request->type) {
                case FavouriteListEnum::PROVIDER:
                    if (FavouriteList::isFavourite(null, $request->providerId, $consumerId)) {
                        return response()->json([
                            'message' => __('static.favorite_list.provider_already_in'),
                            'success' => false,
                        ], 400);
                    }
                    $favouritelist = $this->model->create([
                        'provider_id' => $request->providerId,
                    ]);
                    break;
                case FavouriteListEnum::SERVICE:
                    if (FavouriteList::isFavourite($request->serviceId, null, $consumerId)) {
                        return response()->json([
                            'message' => __('static.favorite_list.service_already_in'),
                            'success' => false,
                        ], 400);
                    }
                    $favouritelist = $this->model->create([
                        'service_id' => $request->serviceId,
                    ]);
                    break;
            }
            $data = $this->model->where('id', $favouritelist->id)->get();
            DB::commit();

            $this->clearFavouriteListCache();

            return response()->json([
                'message' => __('static.favorite_list.store'),
                'success' => true,
            ]);
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {
            $favouriteList = $this->model->findOrFail($id);

            if (!$favouriteList) {
                throw new ExceptionHandler(__('static.favorite_list.id_not_valid'), Response::HTTP_BAD_REQUEST);
            }
            $favouriteList->destroy($id);
            $this->clearFavouriteListCache();

            return response()->json([
                'message' => __('static.favorite_list.destroy'),
                'success' => true,
            ]);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
