<?php

namespace App\Repositories\API;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Jubaer\Zoom\Zoom;
use App\Models\Wallet;
use App\Enums\RoleEnum;
use App\Models\Booking;
use App\Models\Service;
use App\Helpers\Helpers;
use App\Enums\ModuleEnum;
use App\Enums\BookingEnum;
use App\Models\ExtraCharge;
use App\Models\ServiceProof;
use App\Enums\PaymentStatus;
use App\Models\ProviderWallet;
use App\Enums\BookingEnumSlug;
use App\Enums\TransactionType;
use App\Models\ServicemanWallet;
use App\Models\BookingStatusLog;
use App\Events\VerifyProofEvent;
use App\Models\BookingReasonLog;
use App\Enums\WalletPointsDetail;
use App\Http\Traits\BookingTrait;
use App\Events\CreateBookingEvent;
use Illuminate\Support\Facades\DB;
use Modules\Coupon\Entities\Coupon;
use App\Events\AddExtraChargeEvent;
use App\Events\AssignBookingEvent;
use Nwidart\Modules\Facades\Module;
use App\Exceptions\ExceptionHandler;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Events\UpdateServiceProofEvent;
use App\Events\UpdateBookingStatusEvent;
use App\Events\ZoomMeetingCreatedEvent;
use App\Http\Resources\BookingDetailResource;
use App\Models\VideoConsultation;
use Illuminate\Validation\ValidationException;
use Jubaer\Zoom\Facades\Zoom as FacadesZoom;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Modules\Subscription\Entities\UserSubscription;

class BookingRepository extends BaseRepository
{
    use BookingTrait;

    protected $user;
    protected $wallet;
    protected $service;
    protected $settings;
    protected $extraCharge;
    protected $serviceProof;
    protected $bookingStatus;
    protected $providerWallet;
    protected $bookingStatusLog;
    protected $servicemanWallet;
    protected $bookingReasonLog;
    protected $UserSubscription;
    protected $VideoConsultation;

    protected $fieldSearchable = [
        'booking_number' => 'like',
        'service.title' => 'like',
        'payment_method' => 'like',
        'consumer.name' => 'like',
        'payment_status' => 'like',
    ];

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
        $this->user = new User();
        $this->wallet = new Wallet();
        $this->servicemanWallet = new ServicemanWallet();
        $this->providerWallet = new ProviderWallet();
        $this->service = new Service();
        $this->extraCharge = new ExtraCharge();
        $this->settings = Helpers::getSettings();
        $this->serviceProof = new ServiceProof();
        $this->bookingReasonLog = new BookingReasonLog();
        $this->bookingStatusLog = new BookingStatusLog();
        $this->UserSubscription = new UserSubscription();
        $this->VideoConsultation = new VideoConsultation();

        return Booking::class;
    }

    public function getBookingNumber($digits)
    {
        $i = 0;
        do {
            $booking_number = pow(8, $digits) + $i++;
        } while ($this->model->where('booking_number', '=', $booking_number)->first());

        return $booking_number;
    }

    public function show($id)
    {
        try {

            $booking =  $this->model->with(['service' => function ($query) {
                $query->withoutGlobalScopes(['exclude_custom_offers']);
            }, 'videoConsultation'])->findOrFail($id);
            return response()->json([
                "success" => true,
                "data" => new BookingDetailResource($booking)
            ]);

        } catch (\Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getConsumerId($request)
    {
        return $request->consumer_id ?? Helpers::getCurrentUserId();
    }

    public function getUniqueBooking($products)
    {
        return collect($products)->unique(function ($product) {
            return $product['service_id'];
        })->values()->toArray();
    }

    public function createBooking($request)
    {
        DB::beginTransaction();
        try {
            $booking = $this->placeBooking($request);
            $booking = $booking->fresh();
            DB::commit();
            return $this->createPayment($booking, $request);

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function updateCouponUsage($coupon_id)
    {
        return Coupon::findOrFail($coupon_id)->decrement('usage_per_coupon');
    }

    public function isValidCoupon($coupon, $amount, $consumer)
    {
        if (Helpers::couponIsEnable()) {
            if ($coupon && $this->isValidSpend($coupon, $amount)) {
                if ($this->isCouponUsable($coupon, $consumer) && $this->isNotExpired($coupon)) {
                    return true;
                }
            }

            throw new Exception(__('static.booking.coupon_code_should_be_higher', ['code' => $coupon->code, 'min_spend' => $coupon->min_spend]), 422);
        }

        throw new Exception(__('static.booking.coupon_feature_disabled'), 422);
    }

    public function isNotExpired($coupon)
    {
        if ($coupon->is_expired) {
            if (!$this->isOptimumDate($coupon)) {
                throw new Exception(__('static.booking.coupon_code_duration', ['code' => $coupon->code, 'start_date' => $coupon->start_date, 'end_date' => $coupon->end_date]), 422);
            }
        }

        return true;
    }

    public function isOptimumDate($coupon)
    {
        $currentDate = Carbon::now()->format('Y-m-d');
        if (max(min($currentDate, $coupon->end_date), $coupon->start_date) == $currentDate) {
            return true;
        }

        return false;
    }

    public function isValidSpend($coupon, $amount)
    {
        return max($amount, $coupon->min_spend) == $amount;
    }

    public static function getTotalAmount($products)
    {
        $subtotal = [];
        foreach ($products as $product) {
            $singleProductPrice = self::getSalePrice($product);
            $subtotal[] = self::getSubTotal($singleProductPrice, 1);
        }

        return array_sum($subtotal);
    }

    public function isActivePaymentMethod($method)
    {
        $settings = Helpers::getSettings();
        if ($settings['payment_methods'][$method]) {
            return true;
        }

        throw new Exception(__('static.booking.inactive_payment_method'), 400);
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

    public function booking($service, $request)
    {
        $booking_number = (string) $this->getBookingNumber(6);
        $booking = $this->model->create([
            'booking_number' => $booking_number,
            'consumer_id' => $request->consumer_id ?? auth()->user()->id,
            'coupon_id' => $service['coupon_id'] ?? null,
            'provider_id' => $service['provider_id'] ?? null,
            'service_id' => $service['service_id'] ?? null,
            'service_package_id' => $service['service_package_id'] ?? null,
            'address_id' => $service['address_id'] ?? null,
            'service_price' => $service['service_price'] ?? null,
            'type' => $service['type'] ?? 'fixed',
            'tax' => $service['total']['tax'],
            'description' => $service['description'] ?? null,
            'per_serviceman_charge' => $service['per_serviceman_charge'] ?? null,
            'required_servicemen' => $service['total']['required_servicemen'] ?? null,
            'total_extra_servicemen' => $service['total']['total_extra_servicemen'],
            'total_servicemen' => $service['total']['total_servicemen'] ?? null,
            'total_extra_servicemen_charge' => $service['total']['total_serviceman_charge'],
            'coupon_total_discount' => $service['total']['coupon_total_discount'] ?? null,
            'platform_fees' => $service['total']['platform_fees'] ?? null,
            'platform_fees_type' => $service['total']['platform_fees_type'] ?? null,
            'subtotal' => $service['total']['subtotal'],
            'total' => $service['total']['total'],
            'booking_status_id' => Helpers::getbookingStatusIdBySlug(BookingEnumSlug::PENDING),
            'parent_id' => $request->parent_id,
            'date_time' => $this->dateTimeFormater($service['date_time'] ?? null),
            'payment_method' => $request->payment_method,
            'invoice_url' => $this->generateInvoiceUrl($booking_number),
            'created_by_id' => Helpers::getCurrentUserId(),
        ]);
        
        if (!empty($service['servicemen_ids'])) {
            $booking->servicemen()->attach($service['servicemen_ids']);
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

        $booking_status_id = Helpers::getbookingStatusIdBySlug(BookingEnumSlug::PENDING);
        $logData = [
            'title' => 'Pending booking request',
            'description' => 'New booking is added.',
            'booking_id' => $booking->id,
            'booking_status_id' => $booking_status_id,
        ];

        $this->bookingStatusLog->create($logData);
        if(isset($service['additional_services'])){
            foreach ($service['additional_services'] as $additionalService) {
                $booking->additional_services()->attach($additionalService['additional_service_id'], [
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

    public function dateTimeFormater($dateTime)
    {
        return $dateTime ? Carbon::createFromFormat('j-M-Y, g:i a', $dateTime)->format('Y-m-d H:i:s') : null;
    }

    public function storeService($services, $request)
    {
        $booking = null;
        foreach ($services as $service) {
            $booking = $this->booking($service, $request);
        }

        return $booking;
    }

    public function createSubBooking($items, $request, Booking $parentBooking)
    {
        $subBookings = [];
        if (count($items['items']) > 1) {
            foreach ($items['items'] as $item) {
                if (isset($request->products) && $this->isActivePaymentMethod($request->payment_method)) {
                    $booking = $this->storeBooking($item, $request);
                    $subBookings[] = $booking;
                }
            }
        }

        return $subBookings;
    }

    public function getRewardPoints($total)
    {
        $settings = $this->getSettings();
        $minPerOrderAmount = $settings['wallet_points']['min_per_order_amount'];
        $rewardPerOrderAmount = $settings['wallet_points']['reward_per_order_amount'];

        if ($total >= $minPerOrderAmount) {
            $rewardPoints = (int) ($total / $minPerOrderAmount) * $rewardPerOrderAmount;

            return $rewardPoints;
        }
    }

    public function isCouponUsable($coupon, $consumer_id)
    {
        if (!$coupon->is_unlimited) {
            if ($coupon->usage_per_customer) {
                $countUsedPerConsumer = Helpers::getCountUsedPerConsumer($consumer_id, $coupon->id);
                if ($coupon->usage_per_customer <= $countUsedPerConsumer) {
                    return false;
                }
            }

            if ($coupon->usage_per_coupon <= 0) {
                return false;
            }

            return $this->updateCouponUsage($coupon->id);
        }

        return true;
    }

    public function getWalletRatio($settings)
    {
        $walletRatio = $settings['general']['wallet_currency_ratio'];

        return $walletRatio == 0 ? 1 : $walletRatio;
    }

    public function outOfStockMessage($message, $outOfStockProducts)
    {
        return [
            'message' => $message,
            'products' => $outOfStockProducts,
            'success' => false,
        ];
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $booking = $this->model->findOrFail($id);
            if (isset($request['date_time']) || isset($request['address_id'])) {
                if ($booking->booking_status_id === Helpers::getbookingStatusId(BookingEnum::PENDING)) {
                    $date_time = $this->dateTimeFormater($request['date_time'] ?? null);
                    $booking->update([
                        'date_time' => $date_time ?? $booking->date_time,
                        'address_id' => $request['address_id'] ?? $booking->address_id,
                    ]);
                } else {
                    return new ExceptionHandler(__('errors.status_is_not_pending'), 422);
                }
            }

            if (isset($request['booking_status'])) {
                $booking_status = Helpers::getBookingIdBySlug($request['booking_status']);
                $booking_status_id = $booking_status?->id;
                switch ($booking_status?->slug) {
                    case BookingEnumSlug::PENDING:
                        $logData = [
                            'title' => 'Booking is Pending',
                            'description' => 'The booking is in a pending state.',
                        ];
                        break;

                    case BookingEnumSlug::ASSIGNED:
                        $logData = [
                            'title' => 'Booking is Assigned',
                            'description' => 'The booking has been assigned.',
                        ];
                        break;

                    case BookingEnumSlug::ON_THE_WAY:
                        $logData = [
                            'title' => 'Booking is On the Way',
                            'description' => 'The service provider is on the way to the location.',
                        ];
                        break;

                    // case BookingEnumSlug::DECLINE:
                    //     $logData = [
                    //         'title' => 'Booking Declined',
                    //         'description' => 'The booking has been declined.',
                    //     ];
                    //     break;

                    case BookingEnumSlug::CANCEL:
                        $logData = [
                            'title' => 'Booking Canceled',
                            'description' => 'The booking has been canceled.',
                        ];
                        break;

                    case BookingEnumSlug::ON_HOLD:
                        $logData = [
                            'title' => 'Booking On Hold',
                            'description' => 'The booking is on hold.',
                        ];
                        break;

                    case BookingEnumSlug::START_AGAIN:
                        $logData = [
                            'title' => 'Booking Restarted',
                            'description' => 'The booking has been restarted.',
                        ];
                        break;

                    case BookingEnumSlug::ON_GOING:
                        $logData = [
                            'title' => 'Booking On Going',
                            'description' => 'The booking has been on going.',
                        ];
                        break;

                    case BookingEnumSlug::COMPLETED:
                        $logData = [
                            'title' => 'Booking Completed',
                            'description' => 'The booking has been completed.',
                        ];
                        break;

                    case BookingEnumSlug::ACCEPTED:
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
                        return new ExceptionHandler(__('errors.invalid_booking_status'), 422);
                        break;
                }

                $logData['booking_status_id'] = $booking_status_id;
                if ($booking_status?->name  == BookingEnum::CANCEL || $booking_status?->name == BookingEnum::ON_HOLD) {
                    if ($booking_status?->name  == BookingEnum::CANCEL && !Helpers::canCancelBooking($booking)) {
                        throw new Exception(__('static.booking.cancellation_restricted'), 400);
                    }

                    if ($booking->sub_bookings()->count()) {
                        $booking->sub_bookings()?->update([
                            'booking_status_id' => $booking_status_id,
                        ]);

                        foreach ($booking?->sub_bookings()?->get() as $sub_bookings) {
                            $this->bookingReasonLog->create([
                                'booking_id' => $sub_bookings->id,
                                'status_id' => $booking_status_id,
                                'reason' => $request['reason'],
                            ]);

                            $logData['booking_id'] = $sub_bookings->id;
                            $this->bookingStatusLog->create($logData);
                        }
                    } else {
                        $this->bookingReasonLog->create([
                            'booking_id' => $booking->id,
                            'status_id' => $booking_status_id,
                            'reason' => $request['reason'],
                        ]);
                    }
                }
                $logData['booking_id'] = $booking->id;
                $this->bookingStatusLog->create($logData);

                $booking->update([
                    'booking_status_id' => $booking_status_id,
                ]);

                $booking = $booking->fresh();
                event(new UpdateBookingStatusEvent($booking));
            }

            DB::commit();
            $booking = $booking->fresh();
            if ($booking->payment_status == PaymentStatus::COMPLETED && $booking_status?->name == BookingEnum::CANCEL) {
                $this->creditWallet($booking->consumer_id, $booking->total, WalletPointsDetail::ADMIN_CREDIT);
                $booking->payment_status = PaymentStatus::REFUNDED;
                $booking->save();
            }

            if ($booking->payment_status != PaymentStatus::COMPLETED && $booking->booking_status?->slug == BookingEnumSlug::COMPLETED) {
                $this->updatePaymentStatusWithCharge($booking, PaymentStatus::COMPLETED, $booking_status_id);
                $booking->save();
            }

            if ($booking_status_id == Helpers::getbookingStatusId(BookingEnum::COMPLETED)) {
                app(\App\Services\CommissionService::class)->handleCommission($booking);
            }

            return response()->json([
                'message' => __('Status Updated Successfully'),
                'id' => $booking->id,
                'success' => true,
            ]);
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }


    public function updatePaymentStatusWithCharge($booking, $status, $booking_status_id = null)
    {
        $booking->payment_status = $status;
        $booking->sub_bookings()?->update([
            'payment_status' => $status
        ]);
        $booking->extra_charges()?->update([
            'payment_status' => $status
        ]);
        if($booking?->parent){
            $data = ['payment_status' => $status];
            if ($booking_status_id) {
                $data['booking_status_id'] = $booking_status_id;
            }
            $booking->parent->update($data);
        }
    }

    public function calculateCommission()
    {
        try {
            $settings = $this->getSettings();
            $refundableDays = $settings['refund']['refundable_days'];
            $refundableDate = now()->subDays($refundableDays)->toDateString();
            $orderStatusId = $this->getOrderStatusIdByName(config('enums.order_status.delivered'));
            $orders = $this->model->where('payment_status', config('enums.payment_status.completed'))
                ->where('order_status_id', $orderStatusId)
                ->whereNotNull('delivered_at')
                ->whereDate('delivered_at', '<=', $refundableDate)
                ->get();

            foreach ($orders as $order) {
                $this->adminVendorCommission($order);
            }

        } catch (\Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {
            return $this->model->where('id', $id)
                ->where('consumer_id', auth()->user()->id)->destroy($id);
        } catch (\Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getSubscription($item_id)
    {
        $module = Module::find(ModuleEnum::SUBSCRIPTION);
        if (!is_null($module) && $module?->isEnabled()) {
            return $this->UserSubscription?->findOrFail($item_id);
        }

        throw new Exception('Subscription module is inactive', 400);
    }

    public function getInvoice($request)
    {
        try {
            $booking = $this->model->where('booking_number', $request->booking_number)->first();
            if (!$booking) {
                throw new Exception(__('static.booking.invalid_booking_number'), 400);
            }
            $addonsChargeAmount = Helpers::getTotalAddonCharges($booking->id);
            $invoice = [
                'booking' => $booking,
                'settings' => Helpers::getSettings(),
                'addonsChargeAmount' => $addonsChargeAmount,
            ];
            
            return PDF::loadView('emails.invoice', $invoice)->download('invoice-' . $booking->booking_number . '.pdf');
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getCosts($request)
    {
        return $this->calculateCosts($request);
    }


    public function fixedDiscount($subtotal, $couponAmount)
    {
        if ($subtotal >= $couponAmount && $subtotal > 0) {
            return $couponAmount;
        }

        return 0;
    }

    public function percentageDiscount($subtotal, $couponAmount)
    {
        if ($subtotal >= $couponAmount && $subtotal > 0) {
            return ($subtotal * $couponAmount) / 100;
        }

        return 0;
    }

    public function isIncludeOrExclude($coupon, $product)
    {
        if ($coupon->is_apply_all) {
            if (isset($coupon->exclude_products)) {
                if (in_array($product['service_id'], array_column($coupon->exclude_products->toArray(), 'id'))) {
                    return false;
                }
            }

            return true;
        }

        if (isset($coupon->products)) {
            if (in_array($product['service_id'], array_column($coupon->products->toArray(), 'id'))) {
                return true;
            }
        }

        return false;
    }

    public function getWallet($consumer_id)
    {
        $roleName = Helpers::getRoleByUserId($consumer_id);
        if ($roleName == RoleEnum::CONSUMER) {
            return Wallet::firstOrCreate(['consumer_id' => $consumer_id]);
        }

        throw new ExceptionHandler('user must be ' . RoleEnum::CONSUMER, 400);
    }

    public function getVendorWalletBalance($vendor_id)
    {
        return $this->getVendorWallet($vendor_id)->balance;
    }

    public function verifyWallet($consumer_id, $reqWalletBalance)
    {
        $roleName = Helpers::getCurrentRoleName();
        if ($roleName != RoleEnum::PROVIDER) {
            if (Helpers::walletIsEnable()) {
                $walletBalance = $this->getWalletBalance($consumer_id);
                if ($walletBalance >= $reqWalletBalance) {
                    return true;
                }

                throw new Exception(__('static.booking.insufficient_wallet_balance_booking'), 400);
            }

            throw new Exception(__('static.booking.wallet_balance_disabled'), 400);
        }

        throw new Exception(__('static.booking.vendors_wallet_balance_disabled'), 400);
    }

    public function getWalletBalance($consumer_id)
    {
        return $this->getWallet($consumer_id)->balance;
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

    public function debitWallet($consumer_id, $balance, $detail)
    {
        $wallet = $this->getWallet($consumer_id);
        if ($wallet) {
            if ($wallet->balance >= $balance) {
                $wallet->decrement('balance', $balance);
                $this->debitTransaction($wallet, $balance, $detail);

                return $wallet;
            }
            
            throw new ExceptionHandler(__('static.booking.insufficient_wallet_balance_order'), 400);
        }
    }

    public function creditVendorWallet($vendor_id, $balance, $detail)
    {
        $vendorWallet = $this->getVendorWallet($vendor_id);
        if ($vendorWallet) {
            $vendorWallet->increment('balance', $balance);
        }

        $this->creditVendorTransaction($vendorWallet, $balance, $detail);

        return $vendorWallet;
    }

    public function debitVendorWallet($vendor_id, $balance, $detail)
    {
        $vendorWallet = $this->getVendorWallet($vendor_id);
        if ($vendorWallet) {
            if ($vendorWallet->balance >= $balance) {
                $vendorWallet->decrement('balance', $balance);
                $this->debitVendorTransaction($vendorWallet, $balance, $detail);

                return $vendorWallet;
            }

            throw new ExceptionHandler(__('static.booking.insufficient_vendor_wallet_balance'), 400);
        }
    }

    public function getPointAmount($consumer_id)
    {
        return Helpers::formatDecimal($this->getPoints($consumer_id)->balance);
    }

    public function updateBookingPaymentMethod($request)
    {
        $booking = $this->verifyBookingNumber($request->item_id);
        $booking->payment_method = $request->payment_method;
        $booking->save();
        $booking = $booking->fresh();

        return $booking;
    }

    public function rePayment($request)
    {
        try {

            switch ($request->type) {
                case 'booking' || 'extra_charge':
                    $item = $this->updateBookingPaymentMethod($request);
                    break;
                case 'wallet':
                    $item = Wallet::findOrFail($request->item_id);
                    break;
                case 'subscription':
                    $module = Module::find('Subscription');
                    break;
            }

            if (!$item) {
                throw new Exception(__('static.booking.not_found_item'), 400);
            }

            if ($request->type == 'subscription') {
                if (!isset($module)) {
                    throw new Exception(__('static.booking.subscription_module_not_found'), 400);
                }

                $userSubscription = 'Modules\\' . $module->getName() . '\\Entities\\UserSubscription';
                if (class_exists($userSubscription)) {
                    $item = $userSubscription::findOrFail($request->item_id);
                }
            }

            if ($request->type == 'wallet') {
                $transaction = $this->getPaymentTransactions($request->item_id, $request->type);
                $item = Wallet::findOrFail($request->item_id);
                $item['total'] = $transaction->amount;
            }

            $module = Module::find($request->payment_method);
            if (!is_null($module) && $module?->isEnabled()) {
                $moduleName = $module->getName();
                $payment = 'Modules\\' . $moduleName . '\\Payment\\' . $moduleName;

                return $payment::getIntent($item, $request);
            } else {
                throw new Exception(__('static.booking.payment_module_not_found'), 400);
            }

            return $item;
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public static function getPointRatio()
    {
        $settings = Helpers::getSettings();
        $pointRatio = $settings['wallet_points']['point_currency_ratio'];

        return $pointRatio == 0 ? 1 : $pointRatio;
    }

    public function pointsToCurrency($points)
    {
        $pointRatio = $this->getPointRatio();
        return Helpers::formatDecimal($points / $pointRatio);
    }

    public function currencyToPoints($currency)
    {
        $pointRatio = $this->getPointRatio();

        return Helpers::formatDecimal($currency * $pointRatio);
    }

    public function creditPoints($consumer_id, $balance, $detail)
    {
        $points = $this->getPoints($consumer_id);
        if ($points) {
            $points->increment('balance', $balance);
        }

        $this->creditTransaction($points, $balance, $detail);

        return $points;
    }

    public function debitPoints($consumer_id, $currency, $detail)
    {
        $points = $this->getPoints($consumer_id);
        $balance = $this->currencyToPoints($currency);

        if ($points) {
            if ($points->balance >= $balance) {

                $points->decrement('balance', $balance);
                $amount = $this->currencyToPoints($balance);
                $this->debitTransaction($points, $amount, $detail);

                return $points;
            }

            throw new ExceptionHandler(__('static.booking.insufficient_points'), 400);
        }
    }

    public function getRoleId()
    {
        $roleName = Helpers::getCurrentRoleName() ?? RoleEnum::ADMIN;
        if ($roleName == RoleEnum::ADMIN) {
            return User::role(RoleEnum::ADMIN)->first()->id;
        }

        return Helpers::getCurrentUserId();
    }

    public function debitTransaction($model, $amount, $detail, $order_id = null)
    {
        return $this->storeTransaction($model, TransactionType::DEBIT, $detail, $amount, $order_id);
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

    public function debitVendorTransaction($vendorWallet, $amount, $detail, $order_id = null)
    {
        return $this->storeVendorTransaction($vendorWallet, TransactionType::DEBIT, $detail, $amount, $order_id);
    }

    public function creditVendorTransaction($vendorWallet, $amount, $detail, $order_id = null)
    {
        return $this->storeVendorTransaction($vendorWallet, TransactionType::CREDIT, $detail, $amount, $order_id);
    }

    public function storeVendorTransaction($vendorWallet, $type, $detail, $amount)
    {
        return $vendorWallet->transactions()->create([
            'amount' => $amount,
            'vendor_id' => $vendorWallet->vendor_id,
            'detail' => $detail,
            'type' => $type,
            'from' => $this->getRoleId(),
        ]);
    }

    public static function checkRole($request)
    {
        $servicemenIds = $request['servicemen_ids'];
        $usersWithoutServicemanRole = User::whereIn('id', $servicemenIds)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'serviceman');
            })
            ->pluck('id');

        if ($usersWithoutServicemanRole->isNotEmpty()) {
            throw ValidationException::withMessages([
                'servicemen_ids' => __('static.booking.do_not_have_the_serviceman_role'),
            ]);
        }
    }

    public function assign($request)
    {
        DB::beginTransaction();
        try {
            $booking = $this->model->findOrFail($request['booking_id']);

            if ($booking->servicemen()->count()) {
                throw new ExceptionHandler(__('static.booking.servicemen_already_asigned'), 409);
            } else {
                    if(count($request['servicemen_ids']) < $booking->total_servicemen){
                        return response()->json([
                            'message' => __('static.booking.please_asgning_required_servicemen'),
                            'success' => true,
                        ]);
                    }
                    $booking->servicemen()->attach($request['servicemen_ids']);
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
                    DB::commit();
                    $booking->fresh();

                    event(new AssignBookingEvent($booking));
                    event(new UpdateBookingStatusEvent($booking));

                    return response()->json([
                        'message' => __('static.booking.service_asigned_successfull'),
                        'success' => true,
                    ]);

            }
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getInvoiceUrl($booking_number)
    {
        try {
            return $this->verifyBookingNumber($booking_number)->invoice_url;
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function verifyBookingNumber($booking_id)
    {
        try {

            $booking = $this->model->findOrFail($booking_id);
            if (!$booking) {
                throw new Exception(__('static.booking.invalid_booking_number'), 400);
            }

            $booking->service;

            return $booking;
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function addExtraCharges($request)
    {
        DB::beginTransaction();
        try {
            $booking = $this->model::findOrFail($request->booking_id);
            $total = $request->per_service_amount * $request->no_service_done;
        
            $taxes = $booking->taxes;
            $taxAmount  = 0;
            foreach ($taxes as $tax) {
                $taxAmount += $total * $tax->pivot->rate / 100;
            }

            $grandTotal = round($total + $taxAmount, 2);
            
            $extraCharge = $booking->extra_charges()->create([
                'title' => $request->title,
                'booking_id' => $booking->id,
                'per_service_amount' => $request->per_service_amount,
                'no_service_done' => $request->no_service_done,
                'payment_method' => $request->payment_method,
                'payment_status' => PaymentStatus::PENDING,
                'total' => $total,
                'tax_amount' => round($taxAmount, 2),
                'grand_total' => $grandTotal,
            ]);

            $logData = [
                'booking_id' => $booking->id,
                'title' => 'Extra Charge Added',
                'description' => $request->title,
                'booking_status_id' => $booking->booking_status_id,
            ];

            $this->bookingStatusLog->create($logData);
            $request->merge([
                'type' => 'extra_charge',
            ]);

            event(new AddExtraChargeEvent($extraCharge));
            DB::commit();

            return response()->json(['success' => true, 'message' => __('static.booking.charge_added_successfully')]);

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function payment($request)
    {
        if ($request->booking_id) {
            $booking = $this->model->findOrFail($request->booking_id);
            $bookingOfExtraCharges = $booking->extra_charges()->get();

            return $this->createPayment($booking, $request);
        }
    }

    public function addserviceProofs($request)
    {
        try {
            $booking = $this->model->findOrFail($request->booking_id);
            $auth_user_id = Helpers::getCurrentUserId();
            $servicemen = $booking->servicemen()->get();
            if (count($servicemen) > 0) {
                $serviceman_ids = [];
                foreach ($servicemen as $serviceman) {
                    $serviceman_ids[] = $serviceman->id;
                }
                if (in_array($auth_user_id, $serviceman_ids)) {
                    $serviceProof = $booking->serviceProofs()->create([
                        'title' => $request->title,
                        'description' => $request->description,
                    ]);


                    if ($request->hasFile('images_proofs')) {
                        $images = $request->file('images_proofs');
                        foreach ($images as $image) {
                            $serviceProof->addMedia($image)->toMediaCollection('service_proof');
                        }
                        $serviceProof->media;
                    }

                    DB::commit();
                    event(new VerifyProofEvent($booking));

                    return response()->json(['success' => true, 'message' => __('static.booking.service_proof_added')], 200);
                } else {
                    return response()->json(['success' => true, 'message' => __('static.booking.booking_not_assigned')], 200);
                }
            } else {
                return response()->json(['success' => false, 'message' => __('static.booking.servicemen_unavailable')]);
            }
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function updateserviceProofs($request)
    {
        try {
            if ($request->proof_id) {
                $serviceProof = $this->serviceProof->findOrFail($request->proof_id);
                $booking = Helpers::getBookingByIdForProof($serviceProof?->booking_id);
                if ($serviceProof) {
                    $serviceProof->update([
                        'title' => $request->title,
                        'description' => $request->description,
                    ]);

                    if ($request->hasFile('images_proofs')) {
                        $serviceProof->clearMediaCollection('service_proof');
                        $images = $request->file('images_proofs');
                        foreach ($images as $image) {
                            $serviceProof->addMedia($image)->toMediaCollection('service_proof');
                        }
                        $serviceProof->media;
                    }
                    DB::commit();
                    event(new UpdateServiceProofEvent($booking));

                    return response()->json(['success' => true, 'message' => __('static.booking.servicemen_proof_updated')], 200);
                } else {
                    return response()->json(['success' => false, 'message' => __('static.booking.invalid_proof_id')]);
                }
            } else {
                return response()->json(['success' => false, 'message' => __('static.booking.proof_not_provided')]);
            }
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function bookingStep2($request)
    {
        $service = $this->service->withoutGlobalScope('exclude_custom_offers')->findOrFail($request['service_id']);
        $requiredServicemen = (int) $request['required_servicemen'];
        $perServicemanCharge = round($service->price/$requiredServicemen, 2);
        $totalServicemenCharge = round($perServicemanCharge * $requiredServicemen, 2);
        $discountPercent = $service->discount;
        $discountAmount = round(($totalServicemenCharge * $discountPercent) / 100, 2);
        $totalAmount = round($totalServicemenCharge - $discountAmount, 2);

        $addonServices = collect();
        $addonsTotal = 0;

        if (isset($request['additional_services'])) {
            $addonData = collect($request['additional_services']);
            $addonIds = $addonData->pluck('id')->toArray();
            $qtyLookup = $addonData->pluck('qty', 'id');
            $addonServices = $service->additionalServices()
                ->whereIn('id', $addonIds)->get()
                ->map(function ($addon) use ($qtyLookup) {
                    $qty = (int) $qtyLookup->get($addon->id, 1);
                    return [
                        'id' => $addon->id,
                        'title' => $addon->title,
                        'price' => $addon->price,
                        'qty' => $qty,
                        'total_price' => round($addon->price * $qty, 2),
                    ];
                });


            $addonsTotal = $addonServices->sum('total_price');
        }
        return [
            'service_id' => $service->id,
            'per_serviceman_charge' => number_format($perServicemanCharge, 2, '.', ''),
            'required_servicemen' => $requiredServicemen,
            'total_servicemen_charge' => number_format($totalServicemenCharge, 2, '.', ''),
            'discount_percent' => (float) $discountPercent,
            'discount_amount' => number_format($discountAmount, 2, '.', ''),
            'addons_total_amount' => number_format($addonsTotal, 2, '.', ''),
            'addons' => $addonServices->toArray(),
            'total_amount' => number_format($totalAmount+$addonsTotal, 2, '.', ''),
        ];
    }

    public function generateZoomMeeting($request)
    {
        $bookingId = $request->booking_id ?? null;
        $booking = Booking::findOrFail($bookingId);
        $acceptedStatusId = Helpers::getbookingStatusIdBySlug(BookingEnumSlug::ASSIGNED);

        if ($booking->booking_status_id !== $acceptedStatusId) {
            throw new ExceptionHandler(__('errors.zoom_only_for_accepted'), 422);
        }

        if ($booking->type !== 'remotely') {
            throw new ExceptionHandler(__('errors.zoom_only_for_remotely'), 422);
        }

        if (strtolower($booking->payment_method) !== 'cash' && $booking->payment_status !== PaymentStatus::COMPLETED) {
            throw new ExceptionHandler(__('errors.zoom_only_for_completed_payment'), 422);
        }
        
       try {
            $res = FacadesZoom::createMeeting([
                'topic'      => 'Booking #' . $booking->booking_number . ' Consultation',
                'type'       => 2,
                'start_time' => \Carbon\Carbon::parse($booking->date_time)->toDateTimeString(),
                'duration'   => $booking?->service?->duration ?? 30,
                'agenda'     => 'Consultation for booking #' . $booking->booking_number,
                'settings'   => [
                    'host_video'        => true,
                    'participant_video' => true,
                    'waiting_room'      => true,
                ]
            ]);

            if (!isset($res['status']) || !$res['status']) {
                throw new ExceptionHandler(__('errors.zoom_meeting_property_missing'), 422);
            }
            if (isset($res['status']) && $res['status']) {
                $meeting = $res['data'];
                $videoConsultation = $this->VideoConsultation->create([
                    'agenda' => $meeting['agenda'],
                    'meeting_id' => $meeting['id'],
                    'topic' => $meeting['topic'],
                    'type' => $meeting['type'],
                    'platform' => 'zoom',
                    'duration' => $meeting['duration'],
                    'timezone' => $request['timezone'],
                    'password' => $request['password'],
                    'start_time' => $request['start_time'],
                    'settings' => json_encode($meeting['settings']),
                    'start_url' => $meeting['start_url'],
                    'join_url' => $meeting['join_url'],
                ]);
            }

            $booking->update([
                'video_consultation_id' => $videoConsultation->id,
            ]);

            event(new ZoomMeetingCreatedEvent($booking));

            return [
                'success'   => true,
                'message'   => __('errors.zoom_meeting_created'),
            ];

        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), 500);
        }

    }
}
