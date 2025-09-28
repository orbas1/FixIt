<?php

namespace App\Repositories\Backend;

use Exception;
use App\Models\User;
use App\Models\Cart;
use App\Models\Address;
use App\Models\Service;
use App\Enums\RoleEnum;
use App\Models\Category;
use App\Helpers\Helpers;
use App\Enums\CategoryType;
use App\Http\Traits\BookingTrait;
use App\Http\Traits\CheckoutTrait;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;

class PosRepository extends BaseRepository
{
  use BookingTrait;

  public function model()
  {
    $this->cart = new Cart();
    $this->address = new Address();
    return Service::class;
  }


  public function serviceBooking($request)
  {
    $service = $this->model->findOrFail($request->service_id);
    DB::beginTransaction();

    $cartItem = [
        'required_servicemen' => $request->required_servicemen,
        'date_time' => $request?->date_time,
        'address_id' => $request?->address_id,
        'custom_message' => $request?->custom_message,
        'customer_id' => Helpers::getCurrentUserId()
    ];

    $cart = $this->cart->updateOrCreate(
      ['service_id' => $service->id],
      $cartItem
    );
    if ($request?->serviceman_id) {
      $cart->servicemen()->sync($request->serviceman_id);
    }

    DB::commit();

  $cartItems = Cart::where('customer_id' , Helpers::getCurrentUserId())->get();

    return response()->json([
        'html' => view('backend.pos.cart-list', ['cartItems' => $cartItems])->render(),
        'payment_summery' => view('backend.pos.payment-summery',['cartItems' => $cartItems])->render(),
        'status' => 'success',
        'service_id' => $service->id
    ]);
  }

  public function serviceCheckout($request)
  {
    $carts = Cart::where('customer_id' , Helpers::getCurrentUserId())->get();

    $cartItems = [];

    foreach($carts as $index => $cart){
      $servicemen = $cart?->servicemen->pluck('id')->toArray();

      $cartItems[$index]['service_id'] = $cart?->service_id;
      $cartItems[$index]['address_id'] = $cart?->address_id;
      $cartItems[$index]['select_date_time_2'] = "custom";
      $cartItems[$index]['date_time'] = $cart?->date_time;
      $cartItems[$index]['description'] = $cart?->custom_message;
      $cartItems[$index]['select_date_time'] = "custom";
      $cartItems[$index]['required_servicemen'] = $cart?->required_servicemen;
      $cartItems[$index]['select_serviceman'] = "as_per_my_choice";
      $cartItems[$index]['serviceman_id'] = implode(',',$servicemen);
    }
    if (!count($cartItems)) {
      throw new Exception('cart is empty', 400);
    }

    $payload = $this->generateCheckoutPayload($cartItems, $request);
    $booking = $this->placeBooking($payload);

    $booking = $booking->fresh();
    DB::commit();

    $payment = $this->createPayment($booking, $payload);

    Cart::where('customer_id', Helpers::getCurrentUserId())->delete();
    return response()->json([
      'status' => 'success',
  ]);
  }

  public function clearCart($request)
  {
    DB::table('cart')->delete();

    return response()->json([
      'status' => 'success',
  ]);
  }

  public function destroy($id)
  {
      DB::beginTransaction();
      try {
          $cart = $this->cart->findOrFail($id);
          $cart->destroy($id);

          DB::commit();
          $cartItems = Cart::where('customer_id' , Helpers::getCurrentUserId())->get();

          return response()->json([
            'html' => view('backend.pos.cart-list', ['cartItems' => $cartItems])->render(),
            'status' => 'success',
        ]);

      } catch (Exception $e) {

          DB::rollback();

          return back()->with(['error' => $e->getMessage()]);
      }
  }

  public function addAddress($request)
  {
    DB::beginTransaction();
    try {
        if ($request->is_primary) {
          $this->address->where('user_id', $request?->consumer_id)->update(['is_primary' => false]);
        }

        $address = $this->address->create([
          'user_id' => $request?->consumer_id,
          'type' => $request->address_type,
          'postal_code' => $request->postal_code,
          'country_id' => $request->country_id,
          'state_id' => $request->state_id,
          'city' => $request->city,
          'code' => $request->code,
          'address' => $request->address,
          // 'area' => $request->area,
          'alternative_name' => $request->alternative_name,
          'alternative_phone' => $request->phone,
          'is_primary' => $request->is_primary,
      ]);
      DB::commit();
      $consumerId = $request->consumer_id;

      if($consumerId) {
        $user = User::where('id' , $consumerId)->first();
      }

      return response()->json([
        'message' => __('static.address.create_successfully'),
        'address' => $address,
        'html' => view('backend.pos.addresses', compact('user'))->render(),
    ]);
    } catch (Exception $e) {
      DB::rollBack();
      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }
}
