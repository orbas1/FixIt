<?php

namespace App\Repositories\Backend;

use App\Enums\PaymentType;
use App\Enums\RequestEnum;
use App\Enums\RoleEnum;
use App\Enums\WalletPointsDetail;
use App\Events\CreateWithdrawRequestEvent;
use App\Events\UpdateWithdrawRequestEvent;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Traits\WalletPointsTrait;
use App\Models\BankDetail;
use App\Models\WithdrawRequest;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class WithdrawRequestRepository extends BaseRepository
{
    use WalletPointsTrait;

    protected $paymentAccount;

    protected $fieldSearchable = [
        'user.name' => 'like',
        'amount' => 'like',
        'message' => 'like',
    ];

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
        $this->paymentAccount = new BankDetail();

        return WithdrawRequest::class;
    }

    public function show($id)
    {
        try {

            $roleName = Helpers::getCurrentRoleName();
            if ($roleName == RoleEnum::PROVIDER || $roleName == RoleEnum::CONSUMER) {
                return $this->userPaymentAccount($id);
            }

            return $this->model->findOrFail($id);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function verifyPaymentAccount($request, $providerPaymentAccount)
    {
        if (! $providerPaymentAccount) {
            return redirect()->route('backend.withdraw-request.index')->with('warning', 'Please create a payment account before applying for a withdrawal.');
        }

        if ($request->payment_type == PaymentType::PAYPAL && ! $providerPaymentAccount->paypal_email) {
            return redirect()->route('backend.withdraw-request.index')->with('warning', 'Please add a paypal email before applying for a withdrawal.');
        }

        if ($request->payment_type == PaymentType::BANK && ! $providerPaymentAccount->paypal_email) {
            if (! $providerPaymentAccount->account_number || ! $providerPaymentAccount->swift_code || ! $providerPaymentAccount->bank_name || ! $providerPaymentAccount->holder_name) {
                return redirect()->route('backend.withdraw-request.index')->with('warning', 'Please complete a bank detail before applying for a withdrawal.');
            }
        }
        
        return null;
    }   

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $settings = Helpers::getSettings();
            $roleName = auth()->user()->roles->pluck('name')->first();
            $provider_id = $request->provider_id;

            if ($roleName == RoleEnum::PROVIDER) {
                $provider_id = auth()->user()->id;
                $providerPaymentAccount = Helpers::getPaymentAccount($provider_id);
          
                $verificationResult = $this->verifyPaymentAccount($request, $providerPaymentAccount);

                if ($verificationResult) {
                    return $verificationResult; 
                }
            }
            $providerWallet = $this->getProviderWallet($provider_id);
            $providerBalance = $providerWallet->balance;
            $minWithdrawAmount = $settings['provider_commissions']['min_withdraw_amount'];

            if ($providerBalance < $request->amount) {
                return redirect()->back()->with('error', 'Your wallet balance is insufficient for this withdrawal');
            }

            if ($minWithdrawAmount > $request->amount) {
                return redirect()->back()->with('error', "The requested amount must be at least $minWithdrawAmount");
            }

            $withdrawRequest = $this->model->create([
                'amount' => $request->amount,
                'message' => $request->message,
                'status' => RequestEnum::PENDING,
                'provider_id' => $provider_id,
                'payment_type' => $request->payment_type,
                'provider_wallet_id' => $providerWallet->id,
            ]);
            $providerWallet = $this->debitProviderWallet($provider_id, $request->amount, WalletPointsDetail::WITHDRAW);
            event(new CreateWithdrawRequestEvent($withdrawRequest));
            $withdrawRequest->user;

            DB::commit();

            return redirect()->back()->with('message', 'Request Sent Successfully.');
        } catch (Exception $e) {

            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $roleName = auth()->user()->roles->pluck('name')->first();
            $withdrawRequest = $this->model->findOrFail($id);
            if ($roleName == RoleEnum::PROVIDER) {
                return redirect()->back()->with('error', "Unauthorized for $roleName");
            }
            if ($request['submit'] === RequestEnum::APPROVED) {
                $request['status'] = RequestEnum::APPROVED;
            } else {
                $request['status'] = RequestEnum::REJECTED;
            }
            if (isset($request['is_used'])) {
                $request = Arr::except($request, ['is_used']);
            }

            $withdrawRequest->update($request);

            $withdrawRequest = $withdrawRequest->fresh();
            if (! $withdrawRequest->is_used) {
                if ($withdrawRequest->status == RequestEnum::REJECTED) {
                    $this->creditProviderWallet($withdrawRequest->provider_id, $withdrawRequest->amount, WalletPointsDetail::REJECTED);
                }
                $withdrawRequest->is_used = true;
                $withdrawRequest->save();
            }

            $withdrawRequest->total_pending_withdraw_requests = $this->model->where('status', 'pending')->count();
            event(new UpdateWithdrawRequestEvent($withdrawRequest));
            DB::commit();

            return redirect()->back()->with('message', "Successfully $withdrawRequest->status Request");
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {

            $roleName = Helpers::getCurrentRoleName();
            $paymentAccount = $this->model->findOrFail($id);
            if ($roleName == RoleEnum::PROVIDER) {
                $paymentAccount = $this->userPaymentAccount($id);
            }

            return $paymentAccount->destroy($id);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
