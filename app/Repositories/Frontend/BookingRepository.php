<?php

namespace App\Repositories\Frontend;

use Exception;
use App\Models\Booking;
use App\Helpers\Helpers;
use App\Enums\BookingEnum;
use App\Events\CreateBookingEvent;
use App\Http\Traits\BookingTrait;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;

class BookingRepository extends BaseRepository
{
  use BookingTrait;

  public function model()
  {
    return Booking::class;
  }

  public function store($request)
  {
    DB::beginTransaction();

    try {
      $cartItems = session('cartItems', []);
      if (!count($cartItems)) {
        throw new Exception('cart is empty', 400);
      }

      $payload = $this->generateCheckoutPayload($cartItems, $request);
      $booking = $this->placeBooking($payload);

      $booking = $booking->fresh();
      DB::commit();

      $payment = $this->createPayment($booking, $payload);

      if (isset($payment['is_redirect']) || $request->payment_method  == 'cash' || $request->payment_method  == 'wallet') {
        $request->session()->forget('cartItems');
        $request->session()->forget('checkout');
        $request->session()->forget('service_bookings');
        $request->session()->forget('service_package_bookings');
        $request->session()->forget('coupon');
        if ($payment['is_redirect']) {
          return redirect()->away($payment['url']);
        }
      }

      return to_route('frontend.booking.index');

    } catch (Exception $e) {

      DB::rollback();
      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }

  public function update($request, $id)
  {
    DB::beginTransaction();
    try {

      $booking = $this->model->findOrFail($id);
      if ($booking->booking_status_id === Helpers::getbookingStatusId(BookingEnum::PENDING)) {
        if (isset($request['address_id'])) {
          $booking->address_id = $request['address_id'] ?? $booking->address_id;
          $booking->save();
        }

        if ((isset($request['date']) && isset($request['time']))) {
          $dateTime = $request['date'] . " " . $request['time'];
          $booking->date_time = $dateTime ?? $booking->date_time;
          $booking->save();
        }
      }

      $this->updateBookingStatusLogs($request, $booking);
      DB::commit();
      return redirect()->back()->with("message", "Booking update successfully");
    } catch (Exception $e) {

      DB::rollback();
      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }

  public function payment($request)
  {
    try {

      if ($request->booking_id) {
        $booking = $this->model->findOrFail($request->booking_id);
        $request->merge([
          'payment_method' => $booking?->payment_method,
          'type' => 'extra_charge',
          'request_type' => 'web'
        ]);

        $payment = $this->createPayment($booking, $request);
        if(isset($booking->parent_id)){
            event(new CreateBookingEvent($booking));
        }
        
        if (isset($payment['is_redirect'])) {
          if ($payment['is_redirect']) {
            return redirect()->away($payment['url']);
          }
        }

        return to_route('frontend.booking.index');
      }

    } catch (Exception $e) {

      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }
}
