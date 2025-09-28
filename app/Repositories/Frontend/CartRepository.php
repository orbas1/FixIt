<?php

namespace App\Repositories\Frontend;

use App\Models\Service;
use App\Http\Traits\CheckoutTrait;
use Modules\Coupon\Entities\Coupon;
use Prettus\Repository\Eloquent\BaseRepository;

class CartRepository extends BaseRepository
{
  use CheckoutTrait;

  public function model()
  {
    return Service::class;
  }

  public function index($request)
  {
    $checkout = [];
    $serviceBookings = session('service_bookings', []);
    $cartItems = array_merge($serviceBookings, session('service_package_bookings',[]));
    session()?->put('cartItems',  $cartItems);
    if (count($cartItems)) {
      $payload = $this->generateCheckoutPayload($cartItems, $request);
      $checkout = $this->calculate($payload);
      session()?->put('checkout',  $checkout);
    }

    return view('frontend.cart.index', ['serviceBookings' => $serviceBookings, 'checkout' => $checkout, 'cartItems' => $cartItems]);
  }

  public function isCouponExists($code)
  {
    return Coupon::where('code', $code)?->whereNull('deleted_at')?->exists();
  }

  public function addToCart($request)
  {
    $service = $this->model->findOrFail($request->service_id);
    $cartItem = [
        'service' => $service,
        'additional_services' => $request->additional_services,
        'required_servicemen' => $request->required_servicemen,
        'select_serviceman' => $request->select_serviceman,
    ];

    session()?->put('cart',  $cartItem);
    return to_route('frontend.booking.service');
  }

  public function applyCoupon($request)
  {
    $couponCode = $request->coupon;
    if ($this->isCouponExists($couponCode)) {
        session()->put('coupon', $couponCode);
        return redirect()->back()->with('message', 'Coupon applied successfully!');
    } else {
        return redirect()->back()->withErrors(['coupon' => 'Coupon code is invalid']);
    }

    // session()->put('coupon', $request?->coupon);
    // return redirect()->back()->with('message', 'Coupon applied successfully!');
  }

  public function removeCoupon()
  {
    session()->forget('coupon');
    return redirect()->back()->with('message', 'Coupon removed successfully!');
  }
}
