<?php

namespace App\Repositories\API;

use Exception;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class ReviewRepository extends BaseRepository
{
    protected $booking;

    protected $service;

    protected $user;

    protected $fieldSearchable = [
        'rating' => 'like',
        'description' => 'like',
        'provider.provider_name' => 'like',
        'consumer.name' => 'like',
        'consumer.email' => 'like',
        'service.name' => 'like',
    ];

    public function boot()
    {
        try {

            $this->pushCriteria(app(RequestCriteria::class));
        } catch (ExceptionHandler $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function model()
    {
        $this->booking = new Booking();
        $this->service = new Service();
        $this->user = new User();

        return Review::class;
    }

    public function show($id)
    {
        try {

            return $this->model->findOrFail($id);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getProviderIdByServiceId($id)
    {
        return $this->service->findOrFail($id)->user_id;
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $consumer_id = Helpers::getCurrentUserId();
            if (isset($request->serviceman_id)) {
                if (Helpers::isAlreadyReviewedServiceman($consumer_id, $request->serviceman_id)) {
                    $review = $this->model->create([
                        'serviceman_id' => $request->serviceman_id,
                        'consumer_id' => $consumer_id,
                        'rating' => $request->rating,
                        'description' => $request->description,
                    ]);
                    DB::commit();

                    return response()->json([
                        'message' => __('static.review.already_stored'),
                        'data' => $review,
                    ]);
                }
                throw new Exception(__('static.review.already_stored'), 400);
            }
            if (isset($request->service_id)) {
                $provider_id = $this->getProviderIdByServiceId($request->service_id);
                $bookings = Helpers::getConsumerBooking($consumer_id, $request->service_id);
                if ($bookings) {
                    if (Helpers::isBookingCompleted($bookings)) {
                        if (Helpers::isAlreadyReviewed($consumer_id, $request->service_id)) {
                            $review = $this->model->create([
                                'service_id' => $request->service_id,
                                'consumer_id' => $consumer_id,
                                'provider_id' => $provider_id,
                                'rating' => $request->rating,
                                'description' => $request->description,
                            ]);

                            DB::commit();
                            return response()->json([
                                'success' => true,
                                'message' => __('static.review.store'),
                            ]);
                        }

                        throw new Exception(__('static.review.review_already_stored'), 400);
                    }

                    throw new Exception(__('static.review.unpossible_review'), 400);
                }

            }
            throw new Exception(__('static.review.add_service_before_review'), 400);
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $review = $this->model->findOrFail($id);
            $review->update([
                'rating' => $request['rating'] ?? $review->rating,
                'description' => $request['description'] ?? $review->description,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('static.review.updated'),
            ]);
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {
            return $this->model->findOrFail($id)->destroy($id);
            
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function deleteAll($ids)
    {
        try {

            $this->model->whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Your Review was successfully Deleted',
            ]);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
