<?php

namespace App\Http\Traits;

use App\Enums\BookingEnum;
use App\Enums\BookingEnumSlug;
use App\Enums\RoleEnum;
use App\Events\CreateBookingEvent;
use App\Events\UpdateBookingStatusEvent;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\Booking;
use App\Models\BookingReasonLog;
use App\Models\BookingStatusLog;
use App\Models\VideoConsultation;
use Carbon\Carbon;
use Exception;
use Jubaer\Zoom\Facades\Zoom;
use Illuminate\Support\Str;

trait BookingTrait
{
    use CheckoutTrait, PaymentTrait, TransactionsTrait;

    public function getBookingNumber($digits)
    {
        $i = 0;
        do {
            $booking_number = pow(8, $digits) + $i++;
        } while (Booking::where('booking_number', '=', $booking_number)->first());

        return $booking_number;
    }

    public function placeBooking($request)
    {
        try {
            $items = $this->calculate($request);
            $booking = $this->booking($items, $request);
            $this->storeBooking($items, $request, $booking);
            return $booking;

        } catch (Exception $e) {

            throw new ExceptionHandler($e?->getMessage(), $e->getCode());
        }
    }

    public function booking($service, $request)
    {
        $booking_number = (string) $this->getBookingNumber(6);
        $bookingStatusId =  Helpers::getbookingStatusIdBySlug(BookingEnumSlug::PENDING);
        if(isset($booking->parent_id)){
            $booking->provider_id == null;
        }
        $booking = Booking::create([
            'booking_number' => $booking_number,
            'consumer_id' => $request->consumer_id ?? auth()->user()->id,
            'coupon_id' => $service['coupon_id'] ?? null,
            'provider_id' => $service['provider_id'] ?? null,
            'service_id' => $service['service_id'] ?? null,
            'service_package_id' => $service['service_package_id'] ?? null,
            'address_id' => $service['address_id'] ?? null,
            'service_price' => $service['service_price'] ?? null,
            'tax' => $service['total']['tax'],
            'description' => $service['description'] ?? null,
            'per_serviceman_charge' => $service['per_serviceman_charge'] ?? null,
            'required_servicemen' => $service['total']['required_servicemen'] ?? null,
            'total_extra_servicemen' => $service['total']['total_extra_servicemen'],
            'total_servicemen' => $service['total']['total_servicemen'] ?? null,
            'requircreateBookingervicemen' => $service['total']['total_servicemen'] ?? null,
            'total_extra_servicemen_charge' => $service['total']['total_serviceman_charge'],
            'coupon_total_discount' => $service['total']['coupon_total_discount'] ?? null,
            'platform_fees' => $service['total']['platform_fees'] ?? null,
            'platform_fees_type' => $service['total']['platform_fees_type'] ?? null,
            'subtotal' => $service['total']['subtotal'],
            'total' => $service['total']['total'],
            'booking_status_id' => $bookingStatusId,
            'parent_id' => $request->parent_id,
            'date_time' => $this->dateTimeFormater($service['date_time'] ?? null),
            'payment_method' => $request->payment_method,
            'invoice_url' => $this->generateInvoiceUrl($booking_number),
            'created_by_id' => Helpers::getCurrentUserId(),
        ]);
        
        $ids = array_filter($service['servicemen_ids'] ?? [], fn($id) => !empty($id));
        if (!empty($ids)) {
            $booking->servicemen()->attach($ids);
            $booking->servicemen;
        }

        if (!empty($service['taxes']) && is_array($service['taxes'])) {
            foreach ($service['taxes'] as $tax) {
                $booking->taxes()->attach($tax['id'], [
                    'rate' => $tax['rate'],
                    'amount' => $tax['amount']
                ]);
            }
        }

        $logData = [
            'title' => 'Pending booking request',
            'description' => 'New booking is added.',
            'booking_id' => $booking->id,
            'booking_status_id' => $bookingStatusId,
        ];
        BookingStatusLog::create($logData);
        if (isset($service['additional_services'])) {
            foreach ($service['additional_services'] as $additionalService) {
                $booking->additional_services()->attach($additionalService['id'], [
                    'price' => $additionalService['price'],
                    'qty' => $additionalService['qty'],
                    'total_price' => $additionalService['total_price'],
                ]);
            }
        }

        if(isset($booking->parent_id)){
            event(new CreateBookingEvent($booking));
        }
        return $booking;
    }

//  /**
//      * Create a Zoom meeting for a booking
//      */
//     public function createZoomMeeting(Booking $booking)
//     {
//         try {
//             $meeting = Zoom::user()->find('me')->meetings()->create([
//                 'topic'      => $booking->service->name ?? 'Consultation',
//                 'agenda'     => $booking->description ?? 'Video Consultation',
//                 'type'       => 2,
//                 'start_time' => Carbon::parse($booking->date_time)->toIso8601String(),
//                 'duration'   => 30,
//                 'timezone'   => config('app.timezone'),
//                 'password'   => Str::random(8),
//                 'settings'   => [
//                     'join_before_host'  => true,
//                     'host_video'        => true,
//                     'participant_video' => true,
//                     'mute_upon_entry'   => true,
//                     'waiting_room'      => false,
//                 ],
//             ]);

//             VideoConsultation::create([
//                 'meeting_type'  => 'zoom',
//                 'agenda'        => $booking->description ?? 'Video Consultation',
//                 'topic'         => $booking->service->name ?? 'Consultation',
//                 'type'          => 2,
//                 'duration'      => 30,
//                 'timezone'      => config('app.timezone'),
//                 'password'      => $meeting->password ?? null,
//                 'start_time'    => Carbon::parse($booking->date_time),
//                 'settings'      => json_encode($meeting->settings ?? []),
//                 'join_url'      => $meeting->join_url ?? null,
//                 'start_url'     => $meeting->start_url ?? null,
//                 'created_by_id' => $booking->created_by_id,
//             ]);

//         } catch (Exception $e) {
//             throw new Exception('Zoom meeting creation failed: ' . $e->getMessage());
//         }
//     }

    public function dateTimeFormater($dateTime)
    {
        try {
            if (is_null($dateTime)) {
                return null;
            }

            if (preg_match('/^\d{1,2}-[A-Za-z]{3}-\d{4}, \d{1,2}:\d{2} [ap]m$/', $dateTime)) {
                return Carbon::createFromFormat('j-M-Y, g:i a', $dateTime)->format('Y-m-d H:i:s');
            }

            // If already in `Y-m-d H:i:s` format, return as is
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $dateTime)) {
                return $dateTime;
            }

            // If it's in `Y-m-d H:i` format, append ":00" for seconds
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $dateTime)) {
                return $dateTime . ':00';
            }

            // return Carbon::createFromFormat('j-M-Y, g:i a', trim($dateTime))?->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function storeBooking($item, $request, $parentBooking = null)
    {
        $request->merge(['parent_id' => $parentBooking?->id]);
        if (isset($item['services_package'])) {
            foreach ($item['services_package'] as $service_package) {
                $this->storeService($service_package['services'], $request);
            }
        }

        return $this->storeService($item['services'], $request);
    }

    public function storeService($services, $request)
    {
        $booking = null;
        foreach ($services as $service) {
            $booking = $this->booking($service, $request);
        }

        return $booking;
    }

    public function generateInvoiceUrl($booking_number)
    {
        $fullUrl = route('invoice', ['booking_number' => $booking_number]);
        $relativeUrl = str_replace(url('/'), '', $fullUrl);
        return ltrim($relativeUrl, '/');
    }

    // Update Booking Status
    public function updateBookingStatusLogs($request, $booking)
    {
        try {

            if (isset($request['booking_status'])) {
                $booking_status = Helpers::getBookingIdBySlug($request['booking_status']);
                $booking_status_id = $booking_status?->id;
                switch ($booking_status?->name) {
                    case BookingEnum::PENDING:
                        $logData = [
                            'title' => 'Booking is Pending',
                            'description' => 'The booking is in a pending state.',
                        ];
                        break;

                    case BookingEnum::ASSIGNED:
                        $logData = [
                            'title' => 'Booking is Assigned',
                            'description' => 'The booking has been assigned.',
                        ];
                        break;

                    case BookingEnum::ON_THE_WAY:
                        $logData = [
                            'title' => 'Booking is On the Way',
                            'description' => 'The service provider is on the way to the location.',
                        ];
                        break;

                    // case BookingEnum::DECLINE:
                    //     $logData = [
                    //         'title' => 'Booking Declined',
                    //         'description' => 'The booking has been declined.',
                    //     ];
                    //     break;

                    case BookingEnum::CANCEL:
                        $logData = [
                            'title' => 'Booking Canceled',
                            'description' => 'The booking has been canceled.',
                        ];
                        break;

                    case BookingEnum::ON_HOLD:
                        $logData = [
                            'title' => 'Booking On Hold',
                            'description' => 'The booking is on hold.',
                        ];
                        break;

                    case BookingEnum::START_AGAIN:
                        $logData = [
                            'title' => 'Booking Restarted',
                            'description' => 'The booking has been restarted.',
                        ];
                        break;

                    case BookingEnum::ON_GOING:
                        $logData = [
                            'title' => 'Booking On Going',
                            'description' => 'The booking has been on going.',
                        ];
                        break;

                    case BookingEnum::COMPLETED:
                        $logData = [
                            'title' => 'Booking Completed',
                            'description' => 'The booking has been completed.',
                        ];
                        break;

                    case BookingEnum::ACCEPTED:
                        $roleName = Helpers::getCurrentRoleName();
                        if ($roleName == RoleEnum::PROVIDER) {
                            $logData = [
                                'title' => 'Booking Accepted',
                                'description' => 'The booking has been accepted by the provider.',
                            ];
                        } else {
                            $logData = [
                                'title' => 'Booking Accepted',
                                'description' => 'The booking has been accepted by the serviceman.',
                            ];
                        }
                        break;

                    default:
                        throw new Exception(__('errors.invalid_booking_status'), 422);
                        break;
                }

                $logData['booking_status_id'] = $booking_status_id;
                if ($booking_status?->name == BookingEnum::CANCEL || $booking_status?->name == BookingEnum::ON_HOLD) {
                    if ($booking_status?->name == BookingEnum::CANCEL && !Helpers::canCancelBooking($booking)) {
                        throw new Exception(__('static.booking.cancellation_restricted'), 400);
                    }

                    if ($booking->sub_bookings()) {
                        $booking->sub_bookings()?->update([
                            'booking_status_id' => $booking_status_id,
                        ]);

                        $subBookings = $booking?->sub_bookings()?->get();
                        foreach ($subBookings as $subBooking) {
                            BookingReasonLog::create([
                                'booking_id' => $subBooking->id,
                                'status_id' => $booking_status_id,
                                'reason' => $request['reason'],
                            ]);
                            $logData['booking_id'] = $subBooking->id;
                            $this->bookingStatusLog->create($logData);
                        }

                    } else {
                        BookingReasonLog::create([
                            'booking_id' => $booking->id,
                            'status_id' => $booking_status_id,
                            'reason' => $request['reason'],
                        ]);
                    }
                }

                $logData['booking_id'] = $booking->id;
                BookingStatusLog::create($logData);

                $booking->update([
                    'booking_status_id' => $booking_status_id,
                ]);

                $booking = $booking->fresh();
                if ($booking_status_id == Helpers::getbookingStatusId(BookingEnum::COMPLETED)) {
                    app(\App\Services\CommissionService::class)->handleCommission($booking);
                }
                event(new UpdateBookingStatusEvent($booking));
            }
        } catch (Exception $e) {

            throw new ExceptionHandler($e?->getMessage(), $e->getCode());
        }
    }
}
