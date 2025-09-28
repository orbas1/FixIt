<?php

namespace App\Repositories\Frontend;

use Exception;
use App\Models\Review;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionHandler;
use App\Models\Booking;
use App\Models\Service;
use Prettus\Repository\Eloquent\BaseRepository;

class ReviewRepository extends BaseRepository
{
    public function model()
    {
        return Review::class;
    }

    public function getProviderIdByServiceId($id)
    {
        return  Service::findOrFail($id)->pluck('user_id')->first();
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $consumer_id = Helpers::getCurrentUserId();
            if (isset($request->service_id)) {
                $provider_id = $this->getProviderIdByServiceId($request->service_id);
                $bookings = Helpers::getConsumerBooking($consumer_id, $request->service_id);
                if ($bookings) {
                    if (Helpers::isBookingCompleted($bookings)) {
                        if (Helpers::isAlreadyReviewed($consumer_id, $request->service_id)) {
                            $this->model->create([
                                'service_id' => $request->service_id,
                                'consumer_id' => $consumer_id,
                                'provider_id' => $provider_id,
                                'rating' => $request->rating,
                                'description' => $request->description,
                            ]);

                            DB::commit();
                            return redirect()->back()->with('error', __('static.review.store'));
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

            $review=$this->model->findOrFail($id);
            $review->update([
                'rating'=> $request['rating'],
                'description'=> $request['description'],
            ]);

            DB::commit();
            return redirect()->back()->with('message', 'Review Updated Successfully');

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {

            $review=$this->model->findOrFail($id);
            $review->delete();

            return redirect()->back()->with('message', 'Review Deleted Successfully');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}