<?php

namespace App\Repositories\API;

use Exception;
use App\Helpers\Helpers;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionHandler;
use App\Events\CreateServiceRequestEvent;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Http\Resources\ServiceRequestDetailResource;

class ServiceRequestRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title' => 'like',
        'initial_price' => 'like'
    ];

    function model()
    {
        return ServiceRequest::class;
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $serviceRequest = $this->model->create([
                'title' => $request->title,
                'description' => $request->description,
                'duration' => $request->duration,
                'duration_unit' => $request->duration_unit,
                'required_servicemen' => $request->required_servicemen,
                'initial_price' => $request->initial_price,
                'user_id' => Helpers::getCurrentUserId(),
                'booking_date' => $request->booking_date,
                'category_ids' => $request->category_ids
            ]);

            if ($request->image) {
                $images = $request->file('image');
                foreach ($images as $image) {
                    $serviceRequest->addMedia($image)->toMediaCollection('image');
                }
                $serviceRequest->media;
            }

            event(new CreateServiceRequestEvent($serviceRequest));

            DB::commit();
            return response()->json([
                'message' => __('static.service_request.create_successfully'),
                'id' => $serviceRequest->id,
            ]);

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function show($id)
    {
        try {

            $serviceRequest = $this->model->with(['media' ,'bids' ,'user', 'user.media', 'service' => function ($query) { $query->withoutGlobalScope('exclude_custom_offers');}, 'service.media'])->findOrFail($id);
           
            return response()->json([
                'success' => true,
                'data' =>  new ServiceRequestDetailResource($serviceRequest),
            ]);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {

            $serviceRequest = $this->model->findOrFail($id);
            $serviceRequest?->destroy($id);

            return response()->json([
                'success' => true,
                'message' => __('static.service_request.destroy'),
            ]);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
