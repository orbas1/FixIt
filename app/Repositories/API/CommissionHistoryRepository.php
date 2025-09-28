<?php

namespace App\Repositories\API;

use App\Enums\BookingEnum;
use App\Enums\BookingEnumSlug;
use App\Enums\PaymentStatus;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Traits\CommissionTrait;
use App\Models\Booking;
use App\Models\CommissionHistory;
use Exception;
use Illuminate\Support\Facades\Log;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class CommissionHistoryRepository extends BaseRepository
{
    use CommissionTrait;

    protected $fieldSearchable = [
        'Booking.booking_number' => 'like',
        'provider.name' => 'like',
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
        return CommissionHistory::class;
    }

    public function show($id)
    {
        try {

            return $this->model->findOrFail($id);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function store()
    {
        Log::info('Commission cron job ran.');
        $bookingStatusId = Helpers::getbookingStatusIdBySlug(BookingEnumSlug::COMPLETED);
        $bookings = Booking::where('payment_status', PaymentStatus::COMPLETED)->where('booking_status_id', $bookingStatusId)->get();
        if (!$bookings) {
            throw new Exception(__('static.commission_history.only_compare_similar_service'), 400);
        }
        foreach ($bookings as $booking) {
            $this->adminVendorCommission($booking);
        }

    }
}
