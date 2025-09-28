<?php

namespace App\Http\Controllers\Frontend;

use Carbon\Carbon;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CreateServiceBookingRequest;
use App\Http\Requests\Frontend\CreateServicePackageBookingRequest;
use App\Models\Category;
use App\Models\ServicePackage;
use App\Models\TimeSlot;
use App\Repositories\Frontend\BookingRepository;

class BookingController extends Controller
{
    public $repository;

    public function __construct(BookingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $bookings = $this->repository->whereNotNull('parent_id');
        $bookings = $this->filter($bookings, $request);
        $categories = $this->getCategoriesByBookings($bookings);
        $bookings = $bookings->latest('created_at')->paginate($request->paginate);
        return view('frontend.booking.index', ['bookings' => $bookings, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        return $this->repository?->store($request);
    }

    public function update(Request $request, $id)
    {
        return $this->repository?->update($request->all(), $id);
    }

 
    public function service()
    {
        $servicemen = [];
        $cartItem = session('cart', []);
        if (isset($cartItem['select_serviceman'])) {
            if ($cartItem['select_serviceman'] == 'as_per_my_choice') {
                $servicemen = Helpers::getServicemenByProviderId($cartItem['service']?->user_id);
            }
            if($cartItem['service']?->user_id){
                $providerTimeSlot = TimeSlot::where('provider_id', $cartItem['service']?->user_id)->first();
            }
            return view('frontend.booking.service', ['cartItem' => $cartItem, 'servicemen' => $servicemen, 'providerTimeSlot' => $providerTimeSlot]);
        }

        abort(404);
    }

    public function servicePackage(Request $request)
    {
        $package = ServicePackage::where('slug', $request?->slug)->with('user')->whereNull('deleted_at')?->first();
        $providerTimeSlot = TimeSlot::where('provider_id', $package?->provider_id)->first();
        if ($package) {
            $servicemen = Helpers::getServicemenByProviderId($package->provider_id);
            return view('frontend.booking.service-package', ['package' => $package, 'servicemen' => $servicemen,'providerTimeSlot' => $providerTimeSlot]);
        }

        abort(404);
    }

    public function serviceBooking(CreateServiceBookingRequest $request)
    {
        $request->except('_token');
        $serviceBooking = $request->all();
        $currentBookings = session('service_bookings', []);
        $currentBookings = collect($currentBookings)?->map(function ($booking) use ($serviceBooking) {
            if ($booking['service_id'] == $serviceBooking['service_id']) {
                return $serviceBooking;
            }
            return $booking;
        })->all();
        if (!collect($currentBookings)->contains($serviceBooking)) {
            $currentBookings[] = $serviceBooking;
        }
        session()->put('service_bookings', $currentBookings);
        return to_route('frontend.cart.index');
    }

    public function servicePackageBooking(CreateServicePackageBookingRequest $request)
    {
        $requestData = $request->except('_token');
        $currentServicePackageBookings = session('service_package_bookings', []);
        $currentBookings = collect($currentServicePackageBookings);
        $servicePackageId = $requestData['service_packages']['service_package_id'];
        $existingBooking = $currentBookings->firstWhere('service_packages.service_package_id', $servicePackageId);
        if ($existingBooking) {
            $currentBookings = $currentBookings->map(function ($booking) use ($servicePackageId, $requestData) {
                return $booking['service_packages']['service_package_id'] == $servicePackageId ? $requestData : $booking;
            });
        } else {
            // Add new booking to the collection
            $currentBookings->push($requestData);
        }

        session()->put('service_package_bookings', $currentBookings->values()->all());
        $mergedBookings = array_merge(session('service_bookings', []), session('service_package_bookings', []));

        session()->put('cartItems', $mergedBookings);

        return to_route('frontend.cart.index');
    }

    public function payment(Request $request)
    {
        $checkout = session('checkout', []);
        if (count($checkout)) {
            return view('frontend.booking.payment', ['checkout' => $checkout]);
        }

        return redirect()->back()->with('error', 'checkout is empty');
    }

    public function paymentNow(Request $request)
    {
        return $this->repository?->payment($request);
    }

    public function getCategoriesByBookings($bookings)
    {
        return Category::whereHas('services.bookings', function ($query) use ($bookings) {
            $query->whereIn('bookings.id', $bookings->pluck('id'));
        })?->where('category_type', 'service')?->get();
    }

    public function filter($bookings, $request)
    {
        $roleName = Helpers::getCurrentRoleName();
        if ($roleName == RoleEnum::CONSUMER) {
            $bookings = $bookings->where('consumer_id', Helpers::getCurrentUserId());
        }

        // if ($request->start_date && $request->end_date) {
        //     $bookings = $bookings->whereBetween('created_at', [$request->start_date, $request->end_date]);
        // }

        if ($request->start_date && $request->end_date) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $bookings = $bookings->whereBetween('date_time', [$startDate, $endDate]);
        }

        if ($request->categories) {
            $categorySlugs = explode(',', $request->categories);
            $bookings = $bookings->whereHas('service.categories', function ($query) use ($categorySlugs) {
                $query->whereIn('categories.slug', $categorySlugs);
            });
        }

        if (isset($request->status)) {
            $booking_status_id = Helpers::getbookingStatusIdBySlug($request->status);
            $bookings = $bookings->where('booking_status_id', $booking_status_id);
        }

        return $bookings;
    }
}
