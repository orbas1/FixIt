<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Frontend\CartRepository;
use App\Http\Requests\Frontend\ApplyCouponRequest;

class CartController extends Controller
{
    protected $repository;

    public function __construct(CartRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        return $this->repository?->index($request);
    }

    public function addToCart(Request $request)
    {
        return $this->repository?->addToCart($request);
    }

    public function remove(Request $request)
    {
        $serviceId = $request->service_id;
        $serviceBookings = session('service_bookings', []);
        $updatedServiceBookings = array_filter($serviceBookings, function ($serviceBooking) use ($serviceId) {
            return $serviceBooking['service_id'] != $serviceId;
        });
        session(['service_bookings' => $updatedServiceBookings]);
        return redirect()->back()->with('message', 'Service removed from cart');
    }

    public function applyCoupon(ApplyCouponRequest $request)
    {
        return $this->repository?->applyCoupon($request);
    }

    public function removeCoupon()
    {
        return $this->repository?->removeCoupon();
    }

    public function handleCoupon(Request $request)
    {
        // if ($request->has('coupon') && !session()->has('coupon')) {
        //     session()->put('coupon', $request->coupon);
        //     return response()->json(['status' => 'success', 'message' => 'Coupon applied successfully!']);
        // }

        if ($request->has('coupon') && !session()->has('coupon')) {
            $couponCode = $request->coupon;
            if ($this->repository->isCouponExists($couponCode)) {
                session()->put('coupon', $couponCode);
                return response()->json(['status' => 'success', 'message' => 'Coupon applied successfully!']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Coupon code is invalid']);
            }
        }

        if ($request->has('remove_coupon') && session()->has('coupon')) {
            session()->forget('coupon');
            return response()->json(['status' => 'success', 'message' => 'Coupon removed successfully!']);
        }

        return response()->json(['status' => 'error', 'message' => 'No coupon action taken.']);
    }
}
