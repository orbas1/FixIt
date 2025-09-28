<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Models\FavouriteList;
use App\Enums\FavouriteListEnum;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Repositories\Frontend\FavouriteListRepository;

class WishlistController extends Controller
{
    public $repository;

    public function __construct(FavouriteListRepository $repository)
    {
        $this->repository = $repository;
    }

    public function store($type, $id)
    {
        return $this->repository->store($type, $id);
    }

    public function index(Request $request)
    {
        $data = $this->repository->index($request);
        return view('frontend.favourite.index', $data);
    }

    public function destroy($id, $type)
    {
        return $this->repository->destroy($type, $id);
    }


    public function add(Request $request)
    {
        $consumerId = Helpers::getCurrentUserId();
        $data = $request->only(['service_id', 'provider_id']);

        // Check if either service_id or provider_id is provided
        if (isset($data['service_id'])) {
            $serviceId = $data['service_id'];
            $providerId = null;
        } elseif (isset($data['provider_id'])) {
            $providerId = $data['provider_id'];
            $serviceId = null;
        } else {
            return response()->json(['error' => 'No valid ID provided'], 400);
        }

        // Check if the item is already in the wishlist
        if (!FavouriteList::isFavourite($serviceId, $providerId, $consumerId)) {
            FavouriteList::create([
                'consumer_id' => $consumerId,
                'service_id' => $serviceId,
                'provider_id' => $providerId
            ]);
        }

        return response()->json(['status' => 'added']);
    }

    // Remove item from wishlist
    public function remove(Request $request)
    {
        $consumerId = Helpers::getCurrentUserId();
        $data = $request->only(['service_id', 'provider_id']);

        // Check if either service_id or provider_id is provided
        if (isset($data['service_id'])) {
            $serviceId = $data['service_id'];
            $providerId = null;
        } elseif (isset($data['provider_id'])) {
            $providerId = $data['provider_id'];
            $serviceId = null;
        } else {
            return response()->json(['error' => 'No valid ID provided'], 400);
        }

        $favourite = FavouriteList::where('consumer_id', $consumerId)
            ->where(function ($query) use ($serviceId, $providerId) {
                if ($serviceId) {
                    $query->where('service_id', $serviceId);
                }
                if ($providerId) {
                    $query->where('provider_id', $providerId);
                }
            })->first();

        if ($favourite) {
            $favourite->delete();
        }

        return response()->json(['status' => 'removed']);
    }

    // Check if an item is in the wishlist
    public function check(Request $request)
    {
        $consumerId = Helpers::getCurrentUserId();
        $data = $request->only(['service_id', 'provider_id']);

        // Check if either service_id or provider_id is provided
        if (isset($data['service_id'])) {
            $serviceId = $data['service_id'];
            $providerId = null;
        } elseif (isset($data['provider_id'])) {
            $providerId = $data['provider_id'];
            $serviceId = null;
        } else {
            return response()->json(['error' => 'No valid ID provided'], 400);
        }

        $isFavourite = FavouriteList::isFavourite($serviceId, $providerId, $consumerId);

        return response()->json(['isFavourite' => $isFavourite]);
    }
}
