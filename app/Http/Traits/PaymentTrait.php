<?php

namespace App\Http\Traits;

use App\Enums\ModuleEnum;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RoleEnum;
use App\Enums\TransactionType;
use App\Enums\WalletPointsDetail;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\Booking;
use App\Models\PaymentTransactions;
use App\Models\ProviderWallet;
use App\Models\ServicemanWallet;
use App\Models\Wallet;
use Exception;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;

trait PaymentTrait
{
    use TransactionsTrait;

    public function createPayment($booking, $request)
    {
        try {
            if ($request->payment_method != 'cash' && $request->payment_method != 'wallet') {
                $module = Module::find($request->payment_method);
                if (!is_null($module) && $module?->isEnabled()) {
                    $moduleName = $module->getName();
                    $payment = 'Modules\\' . $moduleName . '\\Payment\\' . $moduleName;
                    if (class_exists($payment) && method_exists($payment, 'getIntent')) {
                        if ($request->type == 'extra_charge' && $booking->payment_method != PaymentMethod::COD) {
                            $total = 0;
                            $booking->payment_status = PaymentStatus::PENDING;
                            $booking->save();
                            $bookingOfExtraCharges = $booking->extra_charges()?->get();
                            foreach ($bookingOfExtraCharges as $extraCharge) {
                                $total += $extraCharge->grand_total;
                            }

                            $booking->total = $total;
                        }
                        $request->merge([
                            'type' => 'booking',
                        ]);

                        return $payment::getIntent($booking, $request);
                    } else {
                        throw new Exception(__('static.booking.payment_module_not_found'), 400);
                    }
                }

                throw new Exception('Selected payment module not found or not enable.', 400);
            } elseif ($request->payment_method == 'cash') {
                $request->merge(['type' => 'booking']);
                return $this->paymentStatus($booking, PaymentStatus::PENDING, $request);
            } else if ($request->payment_method == 'wallet') {
                $request->merge(['type' => 'booking']);
                return $this->paymentStatus($booking, PaymentStatus::COMPLETED, $request);
            }

            throw new Exception(__('static.booking.invalid_payment_method'), 400);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function paymentStatus($item, $status, $request)
    {
        if ($status) {
            if ($request->type == 'extra_charge' || $request->type == 'booking') {
                if ($item->payment_method == 'wallet') {
                    $status = PaymentStatus::COMPLETED;

                    $payableAmount = (float) $item->total;

                    if ($request->discount_type && $request->discount_amount !== null) {
                        $discountType = strtolower(trim($request->discount_type));
                        $discountAmount = floatval($request->discount_amount);

                        if ($discountType === 'fixed') {
                            $payableAmount -= $discountAmount;
                        } elseif ($discountType === 'percentage') {
                            $payableAmount -= ($payableAmount * $discountAmount) / 100;
                        }

                        $payableAmount = max(0, $payableAmount);
               
                    }

                    $this->debitWallet($item->consumer_id, $payableAmount, WalletPointsDetail::WALLET_ORDER);
                }
                
                if ($item->parent_id) {
                    $parent = Booking::findOrFail($item->parent_id)->first();
                    $parent?->update([
                        'payment_status' => $status,
                    ]);

                    $parent?->sub_bookings()?->update([
                        'payment_status' => $status,
                    ]);
                }

                $item?->update([
                    'payment_status' => $status,
                ]);

                $item?->sub_bookings()?->update([
                    'payment_status' => $status,
                ]);

                $item?->extra_charges()?->update([
                    'payment_status' => $status
                ]);

            } elseif ($request->type == 'wallet') {
                if ($status == PaymentStatus::COMPLETED) {
                    $item->increment('balance', $request->amount);
                    $this->storeTransaction($item, TransactionType::CREDIT, WalletPointsDetail::TOPUP, $request->amount);
                }
            } elseif($request->type == 'subscription') {
                if($status == PaymentStatus::COMPLETED){
                    $item?->update([
                        'payment_status' => $status,
                        'is_active' => true,
                    ]);
                } else {
                    $item?->update([
                        'payment_status' => $status,
                    ]);
                }
            }
        }

        $item = $item?->fresh();
        return $item;
    }

    public function verifyPayment(Request $request)
    {
        try {

            if ($request->type == 'extra_charge') {
                $request->merge(['is_type' => 'extra_charge']);
                $request->merge(['type' => 'booking']);
            }

            if($request->type == 'booking'){
                $item = Booking::findOrFail($request->item_id);
                if($item->payment_status == 'cash'){
                    $payment_status = PaymentStatus::COMPLETED;
                    $transactions = $this->paymentStatus($item, $payment_status, $request);
                }
            }

            $paymentTransaction = self::getPaymentTransactions($request->item_id, $request?->type);
            if ($paymentTransaction) {
                $payment_method = $paymentTransaction?->payment_method;
                switch ($paymentTransaction?->type) {
                    case 'wallet':
                        $currentRoleName = Helpers::getCurrentRoleName();
                        if ($currentRoleName === RoleEnum::SERVICEMAN) {
                            $item = ServicemanWallet::findOrFail($request->item_id);
                        } else if ($currentRoleName === RoleEnum::PROVIDER) {
                            $item = ProviderWallet::findOrFail($request->item_id);
                        } else {
                            $item = Wallet::findOrFail($request->item_id);
                        }
                        break;
                    case 'subscription':
                        $item = $this->getSubscription($request->item_id);
                        break;
                    case 'booking' || 'extra_charge':
                        $item = Booking::findOrFail($request->item_id);
                }

                if ($item && $payment_method) {
                    if ($payment_method != PaymentMethod::COD && $payment_method != 'wallet') {
                        $payment = Module::find($payment_method);
                        if (!is_null($payment) && $payment?->isEnabled()) {
                            $request['amount'] = $paymentTransaction?->amount;
                            $payment_status = $paymentTransaction?->payment_status;
                            $transactions =  $this->paymentStatus($item, $payment_status, $request);
                            if ($paymentTransaction?->request_type == 'web' && $paymentTransaction?->type == 'booking') {
                                return redirect()->route('frontend.booking.index');
                            }

                            if ($paymentTransaction?->request_type == 'web' && $paymentTransaction?->type == 'wallet') {
                                return redirect()->route('frontend.account.wallet');
                            }

                            return $transactions;
                        }
                    } else if ($payment_method  == 'wallet' || $payment_method  == PaymentMethod::COD) {
                        $payment_status = PaymentStatus::COMPLETED;
                        $transactions = $this->paymentStatus($item, $payment_status, $request);
                        if ($paymentTransaction?->request_type == 'web') {
                            return redirect()->route('frontend.booking.index');
                        }

                        return $transactions;
                    }

                    throw new Exception(__('static.booking.payment_method_not_found'), 400);
                }
            }

            throw new Exception(__('static.booking.invalid_details'), 400);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getSubscription($item_id)
    {
        $module = Module::find(ModuleEnum::SUBSCRIPTION);
        if (!is_null($module) && $module?->isEnabled()) {
            return $this->UserSubscription?->findOrFail($item_id);
        }

        throw new Exception('Subscription module is inactive.', 400);
    }

    public static function updatePaymentStatus($payment, $status)
    {
        if ($payment) {
            $payment?->update([
                'payment_status' => $status,
            ]);

            $payment = $payment?->fresh();
            if ($payment->request_type == 'web') {
                $request = new Request();
                $request->merge(['item_id' => $payment->item_id]);
                $request->merge(['type' => $payment->type]);
                $instance = new self();

                return $instance->verifyPayment($request);
            }

            return $payment;
        }
    }

    public static function updatePaymentMethod($booking, $method)
    {
        $booking?->update([
            'payment_method' => $method,
        ]);

        $booking = $booking->fresh();
        return $booking;
    }

    public static function verifyTransaction($transaction_id)
    {
        return PaymentTransactions::where('transaction_id', $transaction_id)->first();
    }

    public static function getPaymentTransactions($item_id, $type)
    {

        return PaymentTransactions::where([
            'item_id' => $item_id,
            'type' => $type,
        ])?->first();
    }

    public static function updatePaymentStatusByType($item_id, $type, $status)
    {
        $payment = self::getPaymentTransactions($item_id, $type);

        return self::updatePaymentStatus($payment, $status);
    }

    public static function updatePaymentStatusByTrans($transaction_id, $status)
    {
        $payment = self::verifyTransaction($transaction_id);

        return self::updatePaymentStatus($payment, $status);
    }

    public static function updatePaymentTransactionId($payment, $transaction_id)
    {
        if ($payment) {
            $payment?->update([
                'transaction_id' => $transaction_id,
            ]);

            $payment = $payment?->fresh();

            return $payment;
        }
    }
}
