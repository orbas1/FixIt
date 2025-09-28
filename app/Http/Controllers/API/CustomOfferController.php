<?php

namespace App\Http\Controllers\API;

use App\Enums\RoleEnum;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Requests\API\CreateCustomOfferRequest;
use App\Http\Resources\CustomOfferResource;
use App\Models\CustomOffer;
use App\Repositories\API\CustomOfferRepository;

class CustomOfferController extends Controller
{
    public $repository;

    public function __construct(CustomOfferRepository $repository)
    {
        $this->authorizeResource(CustomOffer::class, 'customOffer');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $customOffers = $this->repository->with(['service' => function ($query) {
                $query->withoutGlobalScopes(['exclude_custom_offers']);
            }]);
            $customOffers = $this->filter($customOffers, $request);
            $customOffers = $customOffers->latest('created_at')->simplePaginate($request->paginate ?? $customOffers->count());
            
            return CustomOfferResource::collection($customOffers ?? []);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomOffer $customOffer)
    {
        return $customOffer;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomOffer $customOffer)
    {
       //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCustomOfferRequest $request)
    {
        $customOffer = $this->repository->store($request);

        return response()->json([
            'success' => true,
            'message' => __('static.custom_offer.created_successfully'),
            'service_id' => $customOffer->service_id,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomOffer $customOffer)
    {
        return $this->repository->update($request->all(), $customOffer->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomOffer $customOffer)
    {
        //
    }

    public function filter($customOffers, $request)
    {
        $roleName = Helpers::getCurrentRoleName();
        if ($request->field && $request->sort) {
            $customOffers = $customOffers->orderBy($request->field, $request->sort);
        }

        if ($request->service_request_id) {
            $customOffers = $customOffers->where('service_request_id', $request->service_request_id);
        }

        if ($request->start_date && $request->end_date) {
            $customOffers = $customOffers->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($roleName == RoleEnum::PROVIDER) {
            $customOffers = $customOffers->where('provider_id', Helpers::getCurrentUserId());
        }

        if ($roleName == RoleEnum::CONSUMER) {
            $customOffers = $customOffers->where('user_id', Helpers::getCurrentUserId());
        }

        return $customOffers;
    }

}
