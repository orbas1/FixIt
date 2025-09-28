<?php

namespace App\Repositories\API;

use App\Enums\BidStatusEnum;
use App\Enums\CustomOfferEnum;
use App\Enums\ServiceRequestEnum;
use App\Enums\ServiceTypeEnum;
use App\Events\UpdateBidEvent;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\CustomOffer;
use App\Models\Service;
use App\Models\ServiceRequest;
use Exception;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;


class CustomOfferRepository extends BaseRepository
{
    protected $serviceRequest;

    protected $service;

    public function model()
    {
        $this->serviceRequest = new ServiceRequest();
        $this->service = new Service();
        return CustomOffer::class;
    }

    public function store($request)
    {
        DB::beginTransaction();

        try {
            $requestData = array_merge($request->all(), [
                'user_id' => Helpers::getCurrentUserId()
            ]);

            $customOffer = $this->model->create($requestData);

            if ($customOffer->status === CustomOfferEnum::ACCEPTED) {
                $this->createService($customOffer->id);
            }

            DB::commit();
            return $customOffer->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $customOffer = $this->model->findOrFail($id);
            $customOffer->update($request);
            DB::commit();
            return response()->json([
                'message' => __('static.offer_status_changed'),
                'success' => true
            ]);

        } catch (Exception $e) {

            DB::rollBack();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function createService($customOfferId)
    {
        DB::beginTransaction();
        try {

            $customOffer = $this->model::findOrFail($customOfferId);

            if (!$customOffer || $customOffer->status === CustomOfferEnum::EXPIRED) {
                throw new Exception(__('errors.invalid_custom_offer'), 400);
            }

            $settings = Helpers::getServiceRequestSettings();

            $service = $this->service::create([
                'service_request_id' => $customOffer->id,
                'is_custom_offer' => true,
                'title' => $customOffer->title,
                'description' => $customOffer->description,
                'required_servicemen' => $customOffer->required_servicemen,
                'per_serviceman_commission' => $settings['per_serviceman_commission'],
                'price' => $customOffer->price,
                'service_rate' => $customOffer->price,
                'user_id' => $customOffer->provider_id,
                'created_by_id' => $customOffer->provider_id,
                'duration_unit' => $customOffer->duration_unit,
                'duration' => $customOffer->duration,
                'is_random_related_services' => true,
                'type' => ServiceTypeEnum::FIXED,
                'status' => true,
            ]);

            // Attach related services and categories
            if (!empty($customOffer->category_ids)) {
                $randCategoryId = $customOffer->category_ids[array_rand($customOffer->category_ids)];
                $relatedIds = Helpers::getRelatedServiceId($service, $randCategoryId, $service->id);
                $service->related_services()->attach($relatedIds);
                $service->categories()->attach($customOffer->category_ids);
                // $service->taxes()->attach($settings['default_tax_id']);
            }

            $customOffer->update(['service_id' => $service->id]);

            DB::commit();
            return $service;
        } catch (Exception $e) {

            DB::rollBack();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
