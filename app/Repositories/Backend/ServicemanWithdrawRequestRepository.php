<?php

namespace App\Repositories\Backend;

use App\Enums\PaymentType;
use App\Enums\RequestEnum;
use App\Enums\RoleEnum;
use App\Enums\WalletPointsDetail;
use App\Events\CreateServicemanWithdrawRequestEvent;
use App\Events\UpdateServicemanWithdrawRequestEvent;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Traits\WalletPointsTrait;
use App\Models\BankDetail;
use App\Models\ServicemanWithdrawRequest;
use App\Models\WithdrawRequest;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class ServicemanWithdrawRequestRepository extends BaseRepository
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

        return ServicemanWithdrawRequest::class;
    }

    public function show($id)
    {
        try {

            $roleName = Helpers::getCurrentRoleName();
            if ($roleName == RoleEnum::SERVICEMAN) {
                return $this->userPaymentAccount($id);
            }

            return $this->model->findOrFail($id);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function verifyPaymentAccount($request, $providerPaymentAccount)
    {
        if (!$providerPaymentAccount) {
            return to_route('backend.serviceman-withdraw-request.index')->with('warning', 'Please create a payment account before applying for a withdrawal.');
        }

        if ($request->payment_type == PaymentType::PAYPAL && ! $providerPaymentAccount->paypal_email) {
            return to_route('backend.serviceman-withdraw-request.index')->with('warning', 'Please add a paypal email before applying for a withdrawal.');
        }

        if ($request->payment_type == PaymentType::BANK && ! $providerPaymentAccount->paypal_email) {
            if (! $providerPaymentAccount->account_number || ! $providerPaymentAccount->swift_code || ! $providerPaymentAccount->bank_name || ! $providerPaymentAccount->holder_name) {
                return to_route('backend.serviceman-withdraw-request.index')->with('warning', 'Please complete a bank detail before applying for a withdrawal.');
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
            $serviceman_id = $request->serviceman_id;
            if ($roleName == RoleEnum::SERVICEMAN) {
                $serviceman_id = auth()->user()->id;
                $servicemanPaymentAccount = Helpers::getPaymentAccount($serviceman_id);
                $verificationResult = $this->verifyPaymentAccount($request, $servicemanPaymentAccount);

                if ($verificationResult) {
                    return $verificationResult;
                }
            }

            $servicemanWallet = $this->getServicemanWallet($serviceman_id);

            $servicemanBalance = $servicemanWallet->balance;
            $minWithdrawAmount = $settings['provider_commissions']['min_withdraw_amount'];

            if ($servicemanBalance < $request->amount) {
                return redirect()->back()->with('error', 'Your wallet balance is insufficient for this withdrawal');
            }

            if ($minWithdrawAmount > $request->amount) {
                return redirect()->back()->with('error', "The requested amount must be at least $minWithdrawAmount");
            }

            $withdrawRequest = $this->model->create([
                'amount' => $request->amount,
                'message' => $request->message,
                'status' => RequestEnum::PENDING,
                'serviceman_id' => $serviceman_id,
                'payment_type' => $request->payment_type,
                'serviceman_wallet_id' => $servicemanWallet->id,
            ]);
            $servicemanWallet = $this->debitServicemanWallet($serviceman_id, $request->amount, WalletPointsDetail::WITHDRAW);
            event(new CreateServicemanWithdrawRequestEvent($withdrawRequest));
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
            $servicemanWithdrawRequest = $this->model->findOrFail($id);
            if ($roleName == RoleEnum::SERVICEMAN) {
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

            $servicemanWithdrawRequest->update($request);
            $servicemanWithdrawRequest = $servicemanWithdrawRequest->fresh();
            if (! $servicemanWithdrawRequest->is_used || !$servicemanWithdrawRequest->is_used_by_admin) {
                if ($servicemanWithdrawRequest->status == RequestEnum::REJECTED) {
                    $this->creditServicemanWallet($servicemanWithdrawRequest->serviceman_id, $servicemanWithdrawRequest->amount, WalletPointsDetail::REJECTED);
                }
            }

            $request['is_used_by_admin'] = true;
            if ($roleName == RoleEnum::PROVIDER) {
                $request['is_used_by_admin'] = false;
            }
            $servicemanWithdrawRequest->is_used = true;
            $servicemanWithdrawRequest->save();
            event(new UpdateServicemanWithdrawRequestEvent($servicemanWithdrawRequest));
            DB::commit();

            return redirect()->back()->with('message', "Successfully $servicemanWithdrawRequest->status Request");
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
