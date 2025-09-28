<?php

namespace App\Repositories\Frontend;

use App\Models\Bid;
use App\Models\Service;
use Exception;
use App\Helpers\Helpers;
use App\Enums\BidStatusEnum;
use App\Models\ServicePackage;
use App\Models\ServiceRequest;
use App\Enums\ServiceTypeEnum;
use App\Enums\ServiceRequestEnum;
use Illuminate\Support\Facades\DB;
use App\Events\CreateServiceRequestEvent;
use Illuminate\Support\Facades\Artisan;
use Prettus\Repository\Eloquent\BaseRepository;

class ServiceRequestRepository extends BaseRepository
{
    protected $bid;

    protected $service;

    public function model()
    {
        $this->bid = new Bid();
        $this->service = new Service();
        return ServiceRequest::class;
    }

    public function store($request)
    {
      DB::beginTransaction();
      try {
        $locale = $request->locale ?? app()->getLocale();

                $serviceRequest = $this->model->create([
                'title' => $request->title,
                'description' => $request->description,
                'duration' => $request->duration,
                'duration_unit' => $request->duration_unit,
                'required_servicemen' => $request->required_servicemen,
                'initial_price' => $request->price,
                'user_id' => Helpers::getCurrentUserId(),
                'category_ids' => $request->category_ids,
                'booking_date' => now()->toISOString(),
            ]);

            if ($request->images) {
                $images = $request->file('images');

                foreach ($images as $image) {
                    $serviceRequest->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                }
                $serviceRequest->media;

            }

            DB::commit();
            event(new CreateServiceRequestEvent($serviceRequest));

            return redirect()->back()->with('message', __('frontend::static.home_page.store'));
        } catch (Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
  }

  public function updateBid($request)
  {
    DB::beginTransaction();
    try {
      $bid = $this->bid->findOrFail($request?->bid_id);

      if ($bid->status === BidStatusEnum::REQUESTED) {


        $bid->update(['status' => $request?->status]);
        if($bid->status == BidStatusEnum::REJECTED){
            $serviceRequest = $this->model->findOrFail($bid->service_request_id);

            $serviceRequest->update([
                'status' => ServiceRequestEnum::PENDING
            ]);
        }

        DB::commit();
        $bid = $bid->fresh();
        if ($bid->status == BidStatusEnum::ACCEPTED) {
            $service = $this->createService($bid->service_request_id, $bid);
            return redirect()->back()->with('message',  __('static.bid.updated_successfully'));
        }

        return redirect()->back()->with('message',  __('static.bid.updated_successfully'));

    }

      } catch (Exception $e) {
          DB::rollback();
          return back()->with('error', $e->getMessage());
      }
}

public function createService($service_request_id, $bid)
{
    DB::beginTransaction();
    try {
        $serviceRequest = $this->model::findOrFail($service_request_id);
        $serviceRequestSettings = Helpers::getServiceRequestSettings();

        if ($serviceRequest && $serviceRequest->status != ServiceRequestEnum::CLOSED) {
            $bid = $serviceRequest?->getAcceptedBid();

            $service = $this->service::create([
                'service_request_id' => $serviceRequest->id,
                'title' => $serviceRequest->title,
                'description' => $serviceRequest->description,
                'duration' => $serviceRequest->duration,
                'duration_unit' => $serviceRequest->duration_unit,
                'required_servicemen' => $serviceRequest->required_servicemen,
                'per_serviceman_commission' => $serviceRequestSettings['per_serviceman_commission'],
                'tax_id' => $serviceRequestSettings['default_tax_id'],
                'price' => $bid->amount,
                'service_rate' => $bid->amount,
                'user_id' => $bid->provider_id,
                'created_by_id' => $serviceRequest->user_id,
                'is_random_related_services' => true,
                'type' => ServiceTypeEnum::FIXED,
                'status' => true,
                'is_custom_offer' => true,
            ]);

            $rand_service_id = $serviceRequest->category_ids[array_rand($serviceRequest->category_ids)];
            $related_service_ids = Helpers::getRelatedServiceId($service, $rand_service_id, $service->id);
            $service->related_services()->attach($related_service_ids);

            if (isset($serviceRequest->category_ids)) {
                $service->categories()->attach($serviceRequest->category_ids);
                $service->categories;
            }

            $this->bid->where('service_request_id', $bid->service_request_id)?->whereNot('id', $bid->id)?->update([
                'status' => BidStatusEnum::REJECTED
            ]);

            $serviceRequest->update([
                'status' => ServiceRequestEnum::CLOSED,
                'provider_id' => $bid->provider_id,
                'final_price' => $bid->amount,
                'service_id' => $service->id
            ]);


            DB::commit();

            return $service;

        }

        throw new Exception(__('errors.invalid_service_request'));
    } catch (Exception $e) {

        DB::rollBack();
        throw new Exception($e->getMessage(), $e->getCode());
    }
}

public function delete($id)
{
  DB::beginTransaction();
  try {
    $serviceRequest = $this->model->findOrFail($id);
    $serviceRequest->destroy($id);
    DB::commit();
    return redirect()->back()->with('message',  __('static.bid.deleted_successfully'));
  }

     catch (Exception $e) {
        DB::rollback();
        return back()->with('error', $e->getMessage());
    }
}

}
