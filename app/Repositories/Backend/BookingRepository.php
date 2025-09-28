<?php

namespace App\Repositories\Backend;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Wallet;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\Currency;
use App\Enums\BookingEnum;
use App\Enums\TransactionType;
use App\Enums\BookingEnumSlug;
use App\Exports\BookingExport;
use App\Enums\BookingStatusReq;
use App\Events\AssignBookingEvent;
use App\Models\BookingStatusLog;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use App\Events\BookingReminderEvent;
use App\Exceptions\ExceptionHandler;
use App\Events\UpdateBookingStatusEvent;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class BookingRepository extends BaseRepository
{
    protected $service;

    protected $setting;

    protected $currency;

    protected $user;

    protected $bookingStatusLog;

    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));

        } catch (\Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function model()
    {
        $this->setting = new Setting();
        $this->service = new Service();
        $this->currency = new Currency();
        $this->user = new User();
        $this->bookingStatusLog = new BookingStatusLog();

        return Booking::class;
    }

    public function index($dataTable)
    {
        return $dataTable->render('backend.booking.index');
    }

    public function show($id)
    {
        $booking = $this->model->findOrFail($id);
        $settings = $this->setting->first();
        $default_currency = $this->currency->findOrFail($settings->values['general']['default_currency_id']);

        return view('backend.booking.show', [
            'booking' => $booking,
            'settings' => $settings->values,
            'currency' => $default_currency,
        ]);
    }

    public function getServicemen($id)
    {
        $booking = $this->model->findOrFail($id);
        $provider = $this->user->findOrFail($booking->provider_id);

        $serviceMenData = $provider->servicemans->map(function ($serviceman) {
            return [
                'id' => $serviceman->id,
                'name' => $serviceman->name,
            ];
        });

        return response()->json($serviceMenData);
    }

    public function showChild($id)
    {
        $childBooking = $this->model->findOrFail($id);
        $settings = $this->setting->first();
        $default_currency = $this->currency->findOrFail($settings->values['general']['default_currency_id']);

        return view('backend.booking.child', [
            'childBooking' => $childBooking,
            'settings' => $settings->values,
            'currency' => $default_currency,
        ]);
    }

    public function reminder()
    {
        try {

            $bookings = $this->model->whereNull('deleted_at')->whereDate('date_time', Carbon::today());

            if ($bookings) {
                foreach ($bookings as $booking) {
                    event(new BookingReminderEvent($booking));
                }
            }

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function assignServicemen($request)
    {
        DB::beginTransaction();
        try {
            $booking = $this->model->findOrFail($request['booking_id']);

            if(isset($request['reassign'])){
                $booking->servicemen()->sync($request['servicemen']);
                $booking_status_id = Helpers::getbookingStatusIdBySlug(BookingEnum::ASSIGNED);
                $booking->update([
                    'booking_status_id' => $booking_status_id,
                ]);
                $logData = [
                    'title' => 'Booking is Reassigned',
                    'booking_id' => $booking->id,
                    'description' => 'The booking has been Reassigned.',
                    'booking_status_id' => $booking_status_id,
                ];
                $this->bookingStatusLog->create($logData);
                event(new AssignBookingEvent($booking));
                event(new UpdateBookingStatusEvent($booking));
                DB::commit();
                return redirect()->route('backend.booking.showChild', ['id' => $booking->id])->with('message', __('Serviceman assigned successfully'));

            } else {
                if ($booking->servicemen->isEmpty()) {
                    $booking->servicemen()->attach($request['servicemen']);
                    $booking_status_id = Helpers::getbookingStatusIdBySlug(BookingEnum::ASSIGNED);
                    $booking->update([
                        'booking_status_id' => $booking_status_id,
                    ]);
                    $logData = [
                        'title' => 'Booking is Assigned',
                        'booking_id' => $booking->id,
                        'description' => 'The booking has been assigned.',
                        'booking_status_id' => $booking_status_id,
                    ];
                    $this->bookingStatusLog->create($logData);
                    event(new AssignBookingEvent($booking));
                    event(new UpdateBookingStatusEvent($booking));
                    DB::commit();
                    return redirect()->route('backend.booking.showChild', ['id' => $booking->id])->with('message', __('messages.serviceman assigned successfully'));
                }
            }
            return redirect()->route('backend.booking.showChild', ['id' => $booking->id])->with('error', __('messages.serviceman already assigned'));
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function getStatusLogMessages(): array
    {
        return [
            BookingEnumSlug::PENDING    => ['title' => 'Booking is Pending', 'description' => 'The booking is in a pending state.'],
            BookingEnumSlug::ASSIGNED   => ['title' => 'Booking is Assigned', 'description' => 'The booking has been assigned.'],
            BookingEnumSlug::ON_THE_WAY => ['title' => 'Booking is On the Way', 'description' => 'The service provider is on the way to the location.'],
            // BookingEnumSlug::DECLINE    => ['title' => 'Booking Declined', 'description' => 'The booking has been declined.'],
            BookingEnumSlug::CANCEL     => ['title' => 'Booking Canceled', 'description' => 'The booking has been canceled.'],
            BookingEnumSlug::ON_HOLD    => ['title' => 'Booking On Hold', 'description' => 'The booking is on hold.'],
            BookingEnumSlug::START_AGAIN=> ['title' => 'Booking Restarted', 'description' => 'The booking has been restarted.'],
            BookingEnumSlug::ON_GOING   => ['title' => 'Booking On Going', 'description' => 'The booking is ongoing.'],
            BookingEnumSlug::COMPLETED  => ['title' => 'Booking Completed', 'description' => 'The booking has been completed.'],
        ];
    }

    public function updateBookingStatus($request, $booking_id)
    {
        DB::beginTransaction();
        try {
            $booking = $this->model->findOrFail($booking_id);
            if (!isset($request['booking_status'])) {
                return back()->with('error', __('errors.invalid_booking_status'));
            }
            $user = Auth::user();
            $role = $user->getRoleNames()->first();
            $reqBookingStatusId = Helpers::getbookingStatusIdBySlug($request['booking_status']);
            $bookingStatus = Helpers::getBookingIdBySlug($request['booking_status']);
            $currentStatus = $booking->booking_status->slug;
            $requestedStatus = $bookingStatus->slug;

            $statusMessages = $this->getStatusLogMessages();
            if ($bookingStatus->slug === BookingEnumSlug::ACCEPTED) {
                $logData = [
                    'title'       => 'Booking Accepted',
                    'description' => "The booking status has been changed by the " . ($role ?? RoleEnum::ADMIN) . ".",
                ];
            } elseif (isset($statusMessages[$request['booking_status']])) {
                $logData = [
                    'title'       => $statusMessages[$request['booking_status']]['title'],
                    'description' => $statusMessages[$request['booking_status']]['description'],
                ];
            } else {
                return back()->with('error', __('errors.invalid_booking_status'));
            }

            if($bookingStatus->slug === BookingEnumSlug::ACCEPTED && !in_array($currentStatus, [BookingEnumSlug::PENDING, BookingEnumSlug::ASSIGNED])){
                return back()->with('error', __('errors.booking_cannot_be_accepted'));
            }

            if($bookingStatus->slug === BookingEnumSlug::ASSIGNED && $booking->servicemen->count() <= 0){
                return back()->with('error', __('errors.assign_servicemen_first'));
            }

            if($bookingStatus->slug === BookingEnumSlug::ON_THE_WAY && !in_array($currentStatus, [BookingEnumSlug::ACCEPTED, BookingEnumSlug::ASSIGNED]) && $booking->servicemen->count() <= 0){
                return back()->with('error', __('errors.assign_servicemen_first'));
            }

            if($bookingStatus->slug === BookingEnumSlug::ON_GOING && $currentStatus !== BookingEnumSlug::ON_THE_WAY) {
                return back()->with('error', __('errors.invalid_status_transition_on_going'));
            }

            if($bookingStatus->slug === BookingEnumSlug::CANCEL && $currentStatus !== BookingEnumSlug::PENDING) {
                return back()->with('error', __('errors.cancel_only_if_pending'));
            }

            if($bookingStatus->slug === BookingEnumSlug::ON_HOLD && $currentStatus !== BookingEnumSlug::ON_GOING) {
                return back()->with('error', __('errors.hold_only_if_on_going'));
            }

            if($bookingStatus->slug === BookingEnumSlug::START_AGAIN && $currentStatus !== BookingEnumSlug::ON_HOLD) {
                return back()->with('error', __('errors.start_again_only_if_on_hold'));
            }

            if($bookingStatus->slug === BookingEnumSlug::COMPLETED && !in_array($currentStatus, [BookingEnumSlug::ON_GOING, BookingEnumSlug::ON_HOLD, BookingEnumSlug::START_AGAIN])) {
                return back()->with('error', __('errors.invalid_completion_status'));
            }


            if ($bookingStatus?->name  == BookingEnum::CANCEL || $bookingStatus?->name == BookingEnum::ON_HOLD) {
                if ($bookingStatus?->name  == BookingEnum::CANCEL && !Helpers::canCancelBooking($booking)) {
                    throw new Exception(__('static.booking.cancellation_restricted'), 400);
                }

                if ($booking->sub_bookings()) {
                    $booking->sub_bookings()?->update([
                        'booking_status_id' => $reqBookingStatusId,
                    ]);
                }
            }

            $logData['booking_status_id'] = $reqBookingStatusId;
            $logData['booking_id'] = $booking->id;
            $this->bookingStatusLog->create($logData);
            $booking->update([
                'booking_status_id' => $reqBookingStatusId,
            ]);
            $booking = $booking->refresh();
            if($bookingStatus->slug === BookingEnumSlug::COMPLETED){
                app(\App\Services\CommissionService::class)->handleCommission($booking);
            }
            event(new UpdateBookingStatusEvent($booking));
            DB::commit();
            return redirect()->back()->with('message', __('messages.booking_status_updated'));

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function updatePaymentStatusWithCharge($booking, $status)
    {
        $booking->payment_status = $status;
        $booking->sub_bookings()?->update([
            'payment_status' => $status
        ]);
        $booking->extra_charges()?->update([
            'payment_status' => $status
        ]);
    }

    public function creditWallet($consumer_id, $balance, $detail)
    {
        $wallet = $this->getWallet($consumer_id);
        if ($wallet) {
            $wallet->increment('balance', $balance);
        }

        $this->creditTransaction($wallet, $balance, $detail);

        return $wallet;
    }

    public function getWallet($consumer_id)
    {
        $roleName = Helpers::getRoleByUserId($consumer_id);
        if ($roleName == RoleEnum::CONSUMER) {
            return Wallet::firstOrCreate(['consumer_id' => $consumer_id]);
        }

        throw new ExceptionHandler('user must be ' . RoleEnum::CONSUMER, 400);
    }

    public function creditTransaction($model, $amount, $detail, $order_id = null)
    {
        return $this->storeTransaction($model, TransactionType::CREDIT, $detail, $amount, $order_id);
    }

    public function storeTransaction($model, $type, $detail, $amount, $order_id = null)
    {
        return $model->transactions()->create([
            'amount' => $amount,
            'detail' => $detail,
            'type' => $type,
            'from' => $this->getRoleId(),
        ]);
    }

    public function getRoleId()
    {
        $user = Auth::user();
        $roleName = $user->getRoleNames()->first() ?? RoleEnum::ADMIN;
        if ($roleName == RoleEnum::ADMIN) {
            return User::role(RoleEnum::ADMIN)->first()->id;
        }

        return Helpers::getCurrentUserId();
    }

    public function updateDateTime($request)
    {
        DB::beginTransaction();
        try {
            // Find the booking to update
            $booking = $this->model->findOrFail($request['booking_id']);
            $completedBookingStatusid = Helpers::getbookingStatusIdBySlug(BookingStatusReq::COMPLETED);
            $cancelBookingStatusid = Helpers::getbookingStatusIdBySlug(BookingEnumSlug::CANCEL);

            // Get the provider's existing bookings excluding the current one
            $existingBookings = Booking::where('provider_id', $booking->provider_id)
                ->where('id', '!=', $booking->id)
                ->where('booking_status_id', '!=', $completedBookingStatusid)
                ->where('booking_status_id', '!=', $cancelBookingStatusid)
                ->with('service:id,duration,duration_unit')
                ->get();

            // Requested new date-time
            $newDateTime = Carbon::parse($request['date_time']);

            $bookingDuration = (int) $booking->service->duration;
            // Calculate new end time based on requested booking time
            $newEndTime = $newDateTime->copy();
            if ($booking->service->duration_unit == 'hours') {
                $newEndTime->addHours($bookingDuration);
            } elseif ($booking->service->duration_unit == 'minutes') {
                $newEndTime->addMinutes($bookingDuration);
            }
            // Loop through existing bookings and check for conflicts
            foreach ($existingBookings as $existingBooking) {
                $service = $existingBooking->service;
                if (!$service) continue;

                // Get the existing booking's start time and duration
                $existingStartTime = Carbon::parse($existingBooking->date_time);
                $existingEndTime = $existingStartTime->copy();
                $existingDuration = (int) $service->duration;

                // Add duration based on unit (hours/minutes)
                if ($service->duration_unit == 'hours') {
                    $existingEndTime->addHours($existingDuration);
                } elseif ($service->duration_unit == 'minutes') {
                    $existingEndTime->addMinutes($existingDuration);
                }

                // Check if the new booking time conflicts with an existing one
                if (
                    ($newDateTime->between($existingStartTime, $existingEndTime)) ||
                    ($newEndTime->between($existingStartTime, $existingEndTime)) ||
                    ($existingStartTime->between($newDateTime, $newEndTime))
                ) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Booking time conflicts with an existing booking from ' . $existingStartTime->format('Y-m-d H:i') . ' to ' . $existingEndTime->format('Y-m-d H:i'));
                }
            }

            // Update booking date-time if no conflicts
            $booking->update(['date_time' => $newDateTime]);
            DB::commit();
            return redirect()->route('backend.booking.showChild', ['booking' => $booking->id])->with('message', 'Booking date-time updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function updatePaymentStatus($request)
    {
        $reqPaymentStatus = strtoupper($request['payment_status']);
        $booking = Booking::findOrFail($request['booking_id']);
        $booking->update([
            'payment_status' => $reqPaymentStatus,
        ]);

        return redirect()->route('backend.booking.showChild', ['id' => $booking->id])->with('message', 'Payment status updated successfully.');
    }

    public function export($request)
    {
        try {

            $format = $request->get('format', 'csv');
            switch ($format) {
                case 'excel':
                    return $this->exportExcel();
                case 'csv':
                default:
                    return $this->exportCsv();
            }

        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public  function exportExcel()
    {
        return Excel::download(new BookingExport, 'bookings.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new BookingExport, 'bookings.csv');
    }
}
