<?php

namespace App\Repositories\API;

use App\Enums\PaymentType;
use App\Enums\RequestEnum;
use App\Enums\RoleEnum;
use App\Enums\WalletPointsDetail;
use App\Events\CreateWithdrawRequestEvent;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Traits\WalletPointsTrait;
use App\Models\ServicemanWallet;
use App\Models\WithdrawRequest;
use Exception;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class ServicemanWalletRepository extends BaseRepository
{
    use WalletPointsTrait;

    public function boot()
    {
        try {

            $this->pushCriteria(app(RequestCriteria::class));
        } catch (ExceptionHandler $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function model()
    {
        return ServicemanWallet::class;
    }

    public function topUp($request)
    {
        try {
            $user_id = Helpers::getCurrentUserId();
            $rolename = Helpers::getCurrentRoleName();
            if ($rolename === RoleEnum::SERVICEMAN) {
                $servicemanWallet = $this->getServicemanWallet($user_id);
                if ($servicemanWallet) {
                    $servicemanWallet['total'] = $request->amount;
                    return $this->createPayment($servicemanWallet, $request);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => __('static.provider_wallet.top_up_permission_denied'),
                    ]);
                }
            }
            throw new ExceptionHandler(__('errors.must_be_consumer',['consumer' => RoleEnum::SERVICEMAN]), 400);
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function createPayment($wallet, $request)
    {
        try {
            if ($wallet) {
                $module = Module::find($request->payment_method);
                if (! is_null($module) && $module?->isEnabled()) {
                    $moduleName = $module->getName();
                    $payment = 'Modules\\'.$moduleName.'\\Payment\\'.$moduleName;
                    if (class_exists($payment) && method_exists($payment, 'getIntent')) {
                        $wallet['total'] = $request->amount;

                        return $payment::getIntent($wallet, $request);
                    } else {
                        throw new Exception(__('static.provider_wallet.payment_module_not_found'), 400);
                    }
                }
            }

            throw new Exception(__('static.provider_wallet.invalid_payment_method'), 400);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function withdrawRequest($request)
    {
        DB::beginTransaction();
        try {
            $settings = Helpers::getSettings();
            $roleName = Helpers::getCurrentRoleName();
            if ($roleName == RoleEnum::PROVIDER) {
                $provider_id = Helpers::getCurrentUserId();
                $providerPaymentAccount = Helpers::getPaymentAccount($provider_id);
                $this->verifyPaymentAccount($request, $providerPaymentAccount);

                $servicemanWallet = $this->getProviderWallet($provider_id);
                $providerBalance = $servicemanWallet->balance;
                $minWithdrawAmount = $settings['provider_commissions']['min_withdraw_amount'];

                if ($minWithdrawAmount > $request->amount) {
                    return response()->json([
                        'success' => false,
                        'message' => __('static.provider_wallet.min_withdraw_amount', ['minWithdrawAmount' => $minWithdrawAmount]),
                    ]);
                }

                if ($providerBalance < $request->amount) {
                    return response()->json([
                        'success' => false,
                        'message' => __('static.provider_wallet.insufficient_wallet_balance'),
                    ]);
                }

                $withdrawRequest = WithdrawRequest::Create([
                    'amount' => $request->amount,
                    'message' => $request->message,
                    'status' => RequestEnum::PENDING,
                    'provider_id' => $provider_id,
                    'payment_type' => $request->payment_type,
                    'provider_wallet_id' => $servicemanWallet->id,
                ]);
                $servicemanWallet = $this->debitProviderWallet($provider_id, $request->amount, WalletPointsDetail::WITHDRAW);
                event(new CreateWithdrawRequestEvent($withdrawRequest));
                $withdrawRequest->user;
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => __('static.provider_wallet.withdraw_request_submitted'),
                ]);
            }

            throw new Exception('Selected user must be provider', 400);
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function verifyPaymentAccount($request, $providerPaymentAccount)
    {
        if (! $providerPaymentAccount) {
            return response()->json([
                'success' => false,
                'message' => __('static.provider_wallet.add_payment_account_before_withdrawal'),
            ]);
        }

        if ($request->payment_type == PaymentType::PAYPAL && ! $providerPaymentAccount->paypal_email) {
            return response()->json([
                'success' => false,
                'message' => __('static.provider_wallet.add_paypal_email_before_withdrawal'),
            ]);
        }

        if ($request->payment_type == PaymentType::BANK && ! $providerPaymentAccount->paypal_email) {
            if (
                ! $providerPaymentAccount->account_number || ! $providerPaymentAccount->swift_code
                || ! $providerPaymentAccount->bank_name
                || ! $providerPaymentAccount->holder_name
            ) {
                return response()->json([
                    'success' => false,
                    'message' => __('static.provider_wallet.add_bank_details_before_withdrawal'),
                ]);
            }
        }
    }
}
