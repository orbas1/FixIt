<?php

namespace App\Repositories\API;

use App\Events\CreateServiceRequestEvent;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Resources\ServiceRequestDetailResource;
use App\Models\ServiceRequest;
use App\Services\Security\ContentGuardService;
use DomainException;
use Exception;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

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
            /** @var ContentGuardService $guard */
            $guard = app(ContentGuardService::class);

            $locale = $request->user()?->preferred_locale ?? app()->getLocale();
            $titleResult = $guard->inspect($request->title, ['locale' => $locale]);
            if ($titleResult->isBlocked()) {
                throw new DomainException('Submitted content violates marketplace policies.');
            }

            $descriptionResult = $request->description
                ? $guard->inspect($request->description, ['locale' => $locale])
                : null;

            if ($descriptionResult && $descriptionResult->isBlocked()) {
                throw new DomainException('Submitted content violates marketplace policies.');
            }

            $serviceRequest = $this->model->create([
                'title' => $titleResult->sanitizedText(),
                'description' => $descriptionResult?->sanitizedText() ?? $request->description,
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

            if ($titleResult->shouldEscalate()) {
                $guard->flag($serviceRequest, $titleResult, [
                    'category' => 'service_request_title',
                    'actor_id' => Helpers::getCurrentUserId(),
                ]);
            }

            if ($descriptionResult && $descriptionResult->shouldEscalate()) {
                $guard->flag($serviceRequest, $descriptionResult, [
                    'category' => 'service_request_description',
                    'actor_id' => Helpers::getCurrentUserId(),
                ]);
            }

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
