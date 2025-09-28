<?php

namespace Modules\Paystack\Payment;

use App\Enums\PaymentStatus;
use App\Helpers\Helpers;
use App\Http\Traits\PaymentTrait;
use App\Http\Traits\TransactionsTrait;
use App\Models\PaymentTransactions;
use Exception;
use Modules\Paystack\Enums\PaystackEvent;

class Paystack
{
    use PaymentTrait, TransactionsTrait;

    public static function getPaymentUrl()
    {
        $payment_base_url = env('PAYSTACK_PAYMENT_URL');
        if (env('PAYSTACK_SANDBOX_MODE')) {
            $payment_base_url = 'https://api.paystack.co';
        }

        return $payment_base_url;
    }

    public static function getIntent($obj, $request)
    {
        try {

            $paymentTransaction = PaymentTransactions::updateOrCreate([
                'item_id' => $obj?->id,
                'type' => $request->type,
            ], [
                'item_id' => $obj?->id,
                'transaction_id' => uniqid(),
                'amount' => $obj?->total,
                'payment_method' => config('paystack.name'),
                'payment_status' => PaymentStatus::PENDING,
                'type' => $request->type,
                'request_type' => $request->request_type
            ]);

            $url = self::getPaymentUrl().'/transaction/initialize';
            $intent = [
                'name' => $obj?->consumer['name'],
                'email' => $obj?->consumer['email'],
                'amount' => Helpers::currencyConvert($request->currency_code ?? Helpers::getDefaultCurrencyCode(),Helpers::roundNumber($obj?->total))*100,
                'currency' => 'NGN',
                'callback_url' => route('paystack.webhook', [
                    'item_id' => $obj->id,
                    'type' => $request->type,
                ]),
                'item_id' => $obj?->id,
            ];

            $fields = http_build_query($intent);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer '.env('PAYSTACK_SECRET_KEY'),
                'Cache-Control: no-cache',
            ]);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            $payment = json_decode($response);

            if ($payment->status && empty($err)) {
                $paymentUrl = $payment->data?->authorization_url;
                $transaction_id = $payment->data?->reference;
                self::updatePaymentTransactionId($paymentTransaction, $transaction_id);

                return [
                    'item_id' => $obj?->id,
                    'url' => $paymentUrl,
                    'transaction_id' => $transaction_id,
                    'is_redirect' => true,
                    'type' => $request->type,
                ];
            }

            if ($err) {
                throw new Exception($err, 500);
            }

            throw new Exception('Something went to wrong in paystack gateway', 500);
        } catch (Exception $e) {

            self::updatePaymentStatusByType($obj?->id, $request?->type, PaymentStatus::FAILED);
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function webhook($request)
    {
        try {

            $paymentTransaction = PaymentTransactions::where([
                'item_id' => $request->item_id, 'type' => $request->type,
            ])->first();

            $transaction_id = $paymentTransaction?->transaction_id;
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => self::getPaymentUrl().'/transaction/verify/'.$transaction_id,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer '.env('PAYSTACK_SECRET_KEY'),
                    'Cache-Control: no-cache',
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            $payment = json_decode($response);
            curl_close($curl);
            if ($payment?->status && empty($err)) {
                if ($payment?->data?->status == PaystackEvent::SUCCESS && $payment?->data?->gateway_response == PaystackEvent::SUCCESSFUL) {
                    return self::updatePaymentStatus($paymentTransaction, PaymentStatus::COMPLETED);
                }

                return self::updatePaymentStatus($paymentTransaction, PaymentStatus::PENDING);
            }

            return self::updatePaymentStatus($paymentTransaction, PaymentStatus::FAILED);

        } catch (Exception $e) {

            self::updatePaymentStatusByType($request->item_id, $request->type, PaymentStatus::FAILED);
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
