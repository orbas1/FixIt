<?php

namespace Modules\Subscription\Repositories\API;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Exception;
use Carbon\Carbon;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Enums\TransactionType;
use App\Enums\WalletPointsDetail;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use App\Exceptions\ExceptionHandler;
use App\Http\Traits\WalletPointsTrait;
use App\Models\PaymentTransactions;
use Modules\Subscription\Entities\Plan;
use Modules\Subscription\Enums\PaymentAllowed;
use Prettus\Repository\Eloquent\BaseRepository;
use Modules\Subscription\Entities\UserSubscription;

class SubscriptionRepository extends BaseRepository
{
    use WalletPointsTrait;

    public $plan;

    public function model()
    {
        $this->plan = new Plan();

        return UserSubscription::class;
    }

    public function isAlreadyFreeTrialPurchased()
    {
        return UserSubscription::where('is_included_free_trial', true)?->where('user_id', Helpers::getCurrentUserId())?->exists();
    }

    public function verfiyFreeTrialPlan()
    {
        $settings = Helpers::getSettings();
        if ($settings['subscription_plan']['free_trial_enabled']) {
            if (! $this->isAlreadyFreeTrialPurchased()) {
                return $settings['subscription_plan']['free_trial_days'];
            }
            throw new Exception('Free trial plan all ready purchased.', 400);
        }

        throw new Exception('Free trial plan feature is not enable.', 400);
    }

    public function purchase($request)
    {
        DB::beginTransaction();
        try {

            $addDays = null;
            if ($request->included_free_trial) {
                $addDays = $this->verfiyFreeTrialPlan();
            }

            $userId = Helpers::getCurrentUserId();
            $userRole = Helpers::getRoleByUserId($userId);
            if ($userRole != RoleEnum::PROVIDER) {
                throw new ExceptionHandler("You are not allowed to purchase this subscription plan.", 403);
            }

            $existingSubscription = $this->model
                ->where('user_id', Helpers::getCurrentUserId())
                ->where('is_active', true)
                ->first();

            $plan = Plan::find($request->input('plan_id'));

            if ($existingSubscription) {
                throw new ExceptionHandler("This plan has already been purchased by another provider.", 400);
            }

            $subscription = $this->model->create([
                'user_id' => Helpers::getCurrentUserId(),
                'user_plan_id' => $plan->id,
                'start_date' => Carbon::now(),
                'end_date' => $this->model->calculateEndDate($plan->duration, $addDays),
                'total' => $plan->price,
                'allowed_max_services' => $plan->max_services,
                'allowed_max_addresses' => $plan->max_addresses,
                'allowed_max_servicemen' => $plan->max_servicemen,
                'allowed_max_service_packages' => $plan->max_service_packages,
                'is_active' => false,
                'product_id' => $request?->product_id ?? "",
                'in_app_status' => $request?->in_app_status ?? "",
                'in_app_price' => $request?->in_app_price ?? "",
                'source' => $request?->source ?? "",
            ]);
            DB::commit();
            if ($request->wallet_balance) {
                if ($this->verifyWallet(Helpers::getCurrentUserId(), $request->wallet_balance)) {
                    $this->debitProviderWallet(Helpers::getCurrentUserId(), $request->wallet_balance, WalletPointsDetail::SUBSCRIPTION, null);
                }
            } elseif ($request->payment_method != 'cash' && !$request->source == 'google_play') {
                if (! in_array($request->payment_method, array_column(PaymentAllowed::cases(), 'value'))) {
                    throw new Exception($request->payment_method.' payment method not allow for purchase subscription.', 400);
                }

                $module = Module::find($request->payment_method);
                if (! is_null($module) && $module?->isEnabled()) {
                    $request->merge(['type' => 'subscription']);
                    $moduleName = $module->getName();
                    $payment = 'Modules\\'.$moduleName.'\\Payment\\'.$moduleName;
                    if (class_exists($payment) && method_exists($payment, 'getIntent')) {
                        return $payment::getIntent($subscription, $request);
                    }

                    throw new Exception('Payment module class or method not found.', 400);
                }

                throw new Exception('Selected payment method is invalid', 400);
            } elseif($request->source == 'google_play'){
                $request->merge(['type' => 'subscription']);
                $payment = PaymentTransactions::updateOrCreate([
                    'item_id' => $request?->product_id,
                    'type' => $request?->type,
                ],[
                    'item_id' => $request?->product_id,
                    'transaction_id' => uniqid(),
                    'amount' => $plan?->price,
                    'payment_method' => PaymentMethod::IN_APP_PURCHASE,
                    'payment_status' => PaymentStatus::COMPLETED,
                    'type' => $request->type,
                    'request_type' => $request->request_type
                ]);

                $subscription->is_active = true;
                $subscription->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchased Successfully!',
            ]);

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function debitProviderWallet($consumer_id, $balance, $detail)
    {
        $wallet = $this->getWallet($consumer_id);
        if ($wallet) {
            if ($wallet->balance >= $balance) {
                $wallet->decrement('balance', $balance);
                $this->debitTransaction($wallet, $balance, $detail, $consumer_id);

                return $wallet;
            }

            throw new ExceptionHandler(__('errors.insufficient_wallet_balance'), 400);
        }
    }

    public function debitTransaction($model, $amount, $detail, $consumer_id)
    {
        return $this->storeTransaction($model, TransactionType::DEBIT, $detail, $amount, $consumer_id);
    }

    public function storeTransaction($model, $type, $detail, $amount, $consumer_id)
    {
        return $model->transactions()->create([
            'amount' => $amount,
            'provider_id' => $consumer_id,
            'detail' => $detail,
            'type' => $type,
            'from' => $this->getRoleId(),
        ]);
    }

    public function verifyWallet($consumer_id, $balance)
    {
        if ($balance > 0.00) {
            if (Helpers::walletIsEnable()) {
                $walletBalance = $this->getWalletBalance($consumer_id);
                if ($walletBalance >= $balance) {
                    return true;
                }

                throw new Exception(__('errors.insufficient_wallet_balance'), 400);
            }

            throw new Exception(__('errors.wallet_feature_disabled'), 400);
        }
    }

    public function getPlans($request)
    {
        $plans = $this->plan->where('status', true);

        return $plans->latest('created_at')->paginate($request->paginate ?? $plans->count());
    }

    public function getPlansProductIds($request)
    {
        $plans = $this->plan->where('status', true)->pluck('product_id');

        return $plans;
    }
}
