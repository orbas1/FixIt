<?php

namespace App\Repositories\Frontend;

use Exception;
use App\Helpers\Helpers;
use App\Models\FavouriteList;
use App\Enums\FavouriteListEnum;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;

class FavouriteListRepository extends BaseRepository
{
    function model()
    {
        return FavouriteList::class;
    }

    public function store($type, $id)
    {
        $consumerId = auth()->id();
        $favouriteList = FavouriteList::where('consumer_id', $consumerId)
        ->where('service_id', $id)
        ->first();

    if ($favouriteList) {
        $favouriteList->delete();
        return redirect()->route('frontend.favorite.index')->with('message', 'Successfully Removed From Your Favorite List');
    }else {
            DB::beginTransaction();
            try {
                switch ($type) {
                    case FavouriteListEnum::PROVIDER:
                        $favouritelist = $this->model->create([
                            'consumer_id' => auth()->id(),
                            'provider_id' => $id,
                        ]);
                        break;
                    case FavouriteListEnum::SERVICE:
                        $favouritelist = $this->model->create([
                            'consumer_id' => auth()->id(),
                            'service_id' => $id,
                        ]);
                        break;
                }

                DB::commit();

                return redirect()->route('frontend.favorite.index')->with('message', 'Successfully Added to Your Favourite List');
            } catch (Exception $e) {
                DB::rollback();
                throw new ExceptionHandler($e->getMessage(), $e->getCode());
            }
        }
    }


    public function index($request)
    {
        $queryProviders = $this->model::where('consumer_id', auth()->id())
            ->whereNotNull('provider_id')
            ->latest('created_at');

        $queryServices = $this->model::where('consumer_id', auth()->id())
            ->whereNotNull('service_id')
            ->latest('created_at');

        $providers = $queryProviders->paginate(Helpers::getThemeOptions()['pagination']['provider_per_page'] ?? $queryProviders?->count());
        $services = $queryServices->paginate(Helpers::getThemeOptions()['pagination']['service_per_page'] ?? $queryServices?->count());
        return [
            'providers' => $providers,
            'services' => $services,
        ];
    }

    public function destroy($type, $id)
    {
        try {
            $favouriteList = FavouriteList::where('consumer_id', auth()->user()->id)
                ->where(function ($query) use ($type, $id) {
                    if ($type === FavouriteListEnum::PROVIDER) {
                        $query->where('provider_id', $id);
                    } elseif ($type === FavouriteListEnum::SERVICE) {
                        $query->where('service_id', $id);
                    }
                })
                ->get();

            if ($favouriteList->count() > 0) {
                $favouriteList->first()->delete();
            }

            return redirect()->route('frontend.favorite.index')->with('message', 'Successfully Removed From Your Favourite List');
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
