<?php

namespace App\Http\Controllers\API;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\AddExtraChargeRequest;
use App\Http\Requests\API\AssigningServicemenRequest;
use App\Http\Requests\API\CreateBookingRequest;
use App\Http\Requests\API\GenerateZoomMeetingRequest;
use App\Http\Requests\API\UpdateBookingRequest;
use App\Http\Requests\API\validateBookingStep2;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\BookingStatus;
use App\Repositories\API\BookingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Jubaer\Zoom\Facades\Zoom;

class BookingController extends Controller
{
    public $repository;

    public function __construct(BookingRepository $repository)
    {
        $this->repository = $repository;
        $this->authorizeResource(Booking::class, 'booking', [
            'except' => ['store'],
        ]);
    }

    public function index(Request $request)
    {
        $bookings = $this->repository->whereNotNull('parent_id');

        $roleName = Helpers::getCurrentRoleName();
        if ($roleName == RoleEnum::CONSUMER) {
            $bookings = $bookings->where('consumer_id', Helpers::getCurrentUserId());
        }

        if ($roleName == RoleEnum::PROVIDER) {
            $bookings = $this->repository->whereNotNull('parent_id')->where('provider_id', Helpers::getCurrentProviderId());
        }

        if ($roleName == RoleEnum::SERVICEMAN) {
            $servicemanId = Helpers::getCurrentUserId();
            $bookings = $bookings->whereNotNull('parent_id')->whereHas('servicemen', function ($query) use ($servicemanId) {
                    $query->where('serviceman_id', $servicemanId);
                });
        }

        // Filters
        if ($request->filled('provider_id')) {
            $bookings->where('provider_id', $request->provider_id);
        }

        if ($request->filled('serviceman_id')) {
            $bookings->whereHas('servicemen', function ($query) use ($request) {
                $query->where('serviceman_id', $request->serviceman_id);
            });
        }

        if ($request->filled('category_id')) {
            $bookings->whereHas('service.categories', function ($query) use ($request) {
                $query->where('categories.id', $request->category_id);
            });
        }

        if ($request->filled('booking_status')) {
            $statusId = BookingStatus::where('slug', $request->booking_status)->value('id');
            if ($statusId) {
                $bookings->where('booking_status_id', $statusId);
            }
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $bookings->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('time_filter')) {
            $today = now()->startOfDay();
            $tomorrow = now()->addDay()->startOfDay();
            $endOfTomorrow = now()->addDay()->endOfDay();

            switch (strtolower($request->time_filter)) {
                case 'today':
                    $bookings->whereNotNull('date_time')
                            ->whereDate('date_time', now()->toDateString());
                    break;

                case 'tomorrow':
                    $bookings->whereNotNull('date_time')
                            ->whereDate('date_time', $tomorrow->toDateString());
                    break;

                case 'upcoming':
                    $bookings->whereNotNull('date_time')
                            ->where('date_time', '>', $endOfTomorrow);
                    break;

                case 'past':
                    $bookings->whereNotNull('date_time')
                            ->where('date_time', '<', $today);
                    break;

                case 'all':
                default:
                    break;
            }
        }

        // Load only required relationships and columns
        $bookings = $bookings
            ->select([
                'id', 'booking_number', 'required_servicemen', 'subtotal', 'total', 
                'date_time', 'payment_method', 'payment_status', 'booking_status_id', 
                'service_id', 'parent_id','provider_id', 'date_time', 'address_id', 'consumer_id'
            ])
            ->with([
                'service' => function ($query) {
                    $query->withoutGlobalScope('exclude_custom_offers')
                        ->select('id', 'title', 'price', 'service_rate');
                },
                'service.media',
                'booking_status:id,name,slug',
                'provider',
                'provider.media',
                'servicemen',
                'address'
            ])
            ->latest('created_at')
            ->simplePaginate($request->input('paginate', $bookings->count()));

        return BookingResource::collection($bookings);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateBookingRequest $request)
    {
        return $this->repository->createBooking($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking)
    {
        return $this->repository->show($booking->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        return $this->repository->update($request->all(), $booking->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Booking $booking)
    {
        return $this->repository->destroy($booking->getId($request));
    }

    /**
     * Update Status the specified resource from storage.
     *
     * @param  int  $id
     * @param  int  $status
     * @return \Illuminate\Http\Response
     */
    public function status($id, $status)
    {
        return $this->repository->status($id, $status);
    }

    public function calculateCommission()
    {
        return $this->repository->calculateCommission();
    }

    public function filter($bookings, $request)
    {
        $roleName = Helpers::getCurrentRoleName();
        if ($roleName == RoleEnum::CONSUMER) {
            $bookings = $bookings->where('consumer_id', Helpers::getCurrentUserId());
        }

        if ($roleName == RoleEnum::PROVIDER) {
            $bookings = $this->repository->whereNotNull('parent_id')->where('provider_id', Helpers::getCurrentProviderId());
        }

        if ($roleName == RoleEnum::SERVICEMAN) {
            $servicemanId = Helpers::getCurrentUserId();
            $bookings = $bookings->whereNotNull('parent_id')
                ->whereHas('servicemen', function ($query) use ($servicemanId) {
                    $query->where('serviceman_id', $servicemanId);
                });
        }

        if ($request->field && $request->sort) {
            $bookings = $bookings->orderBy($request->field, $request->sort);
        }

        if (isset($request->status)) {
            $booking_status_id = Helpers::getbookingStatusId($request->status);
            $bookings = $bookings->where('booking_status_id', $booking_status_id);
        }

        if ($request->start_date && $request->end_date) {
            $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            $bookings = $bookings->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->category_ids) {
            $categoryIds = is_array($request->category_ids) ? $request->category_ids : [$request->category_ids];

            $bookings = $bookings->whereHas('service.categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            });
        }

        return $bookings;
    }

    public function rePayment(Request $request)
    {
        return $this->repository->rePayment($request);
    }

    public function payment(Request $request)
    {
        return $this->repository->payment($request);
    }

    public function verifyPayment(Request $request)
    {
        return $this->repository->verifyPayment($request);
    }

    public function assign(AssigningServicemenRequest $request)
    {
        return $this->repository->assign($request->all());
    }

    public function getInvoiceUrl(Request $request)
    {
        return $this->repository->getInvoiceUrl($request->booking_number);
    }

    public function getInvoice(Request $request)
    {
        return $this->repository->getInvoice($request);
    }

    public function addExtraCharges(AddExtraChargeRequest $request)
    {
        return $this->repository->addExtraCharges($request);
    }

    public function addserviceProofs(Request $request)
    {
        return $this->repository->addserviceProofs($request);
    }

    public function updateserviceProofs(Request $request)
    {
        return $this->repository->updateserviceProofs($request);
    }

    public function deleteExtraCharge(Request $request, Booking $booking, $chargeId)
    {
        try {
            $extraCharge = $booking->extra_charges()->where('id', $chargeId)->first();

            if (!$extraCharge) {
                return response()->json([
                    'success' => false,
                    'message' => __('static.booking.extra_charge_not_found')
                ], 404);
            }

            $extraCharge->delete();

            return response()->json([
                'success' => true,
                'message' => __('static.booking.extra_charge_deleted')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('static.booking.something_went_wrong_while_deleting_extra_charge'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bookingStep2(validateBookingStep2 $request)
    {
        return $this->repository->bookingStep2($request->all());
    }

    public function generateZoomMeeting(GenerateZoomMeetingRequest $request)
    {
        return $this->repository->generateZoomMeeting($request);
    }
}
