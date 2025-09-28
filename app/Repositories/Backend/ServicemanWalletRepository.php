<?php

namespace App\Repositories\Backend;

use App\Enums\RoleEnum;
use App\Enums\TransactionType;
use App\Enums\WalletPointsDetail;
use App\Helpers\Helpers;
use App\Models\ServicemanWallet;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class ServicemanWalletRepository extends BaseRepository
{
    protected $user;

    public function model()
    {
        $this->user = new User();

        return ServicemanWallet::class;
    }

    public function index($dataTable)
    {
        $servicemen = $this->user->role(RoleEnum::SERVICEMAN)->get();
        $serviceman = $this->model->where('serviceman_id', request()->serviceman_id)->first();

        return $dataTable->render('backend.serviceman-wallet.index', ['servicemen' => $servicemen, 'balance' => $serviceman->balance ?? null]);
    }

    public function getRoleId()
    {
        $roleName = Helpers::getCurrentRoleName() ?? RoleEnum::ADMIN;
        if ($roleName == RoleEnum::ADMIN) {
            return $this->user->role(RoleEnum::ADMIN)->first()->id;
        }

        return Helpers::getCurrentUserId();
    }

    public function creditOrDebit($request)
    {
        try {
            if ($request->serviceman_id) {
                if ($request->type == 'credit') {
                    $wallet = $this->creditWallet($request->serviceman_id, $request->balance, $request->note ?? WalletPointsDetail::ADMIN_CREDIT);
                    if ($wallet) {
                        $wallet->setRelation('transactions', $wallet->transactions()->paginate($request->paginate ?? $wallet->transactions()->count()));
                    }

                    return redirect()->back()->with('message', 'Credited Successfully');
                } else {

                    return $wallet = $this->debitWallet($request->serviceman_id, $request->balance, $request->note ?? WalletPointsDetail::ADMIN_DEBIT, $request);
                }
            } else {
                return back()->with('error', 'Please select provider first');
            }
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function getWallet($serviceman_id)
    {
        $roleName = Helpers::getRoleByUserId($serviceman_id);
        if ($roleName == RoleEnum::SERVICEMAN) {
            return $this->model->firstOrCreate(['serviceman_id' => $serviceman_id]);
        }

        return back()->with('error', 'user must be Serviceman');
    }

    public function creditTransaction($model, $amount, $detail, $booking_id = null)
    {
        return $this->storeTransaction($model, TransactionType::CREDIT, $detail, $amount, $booking_id);
    }

    public function creditWallet($serviceman_id, $balance, $detail)
    {
        $wallet = $this->getWallet($serviceman_id);

        if ($wallet) {
            $wallet->increment('balance', $balance);
        }

        $this->creditTransaction($wallet, $balance, $detail);

        return $wallet;
    }

    public function debitWallet($serviceman_id, $balance, $detail, $request)
    {
        $wallet = $this->getWallet($serviceman_id);
        if ($wallet) {
            if ($wallet->balance >= $balance) {
                $wallet->decrement('balance', $balance);
                $this->debitTransaction($wallet, $balance, $detail);
                $wallet->setRelation('transactions', $wallet->transactions()
                    ->paginate($request->paginate ?? $wallet->transactions()->count()));

                return redirect()->back()->with('message', 'Debited Successfully');
            }

            return redirect()->back()->with('error', 'Balance is not sufficient for this withdrawal.');
        }
    }

    public function debitTransaction($model, $amount, $detail, $order_id = null)
    {
        return $this->storeTransaction($model, TransactionType::DEBIT, $detail, $amount, $order_id);
    }

    public function storeTransaction($model, $type, $detail, $amount, $order_id = null)
    {
        return $model->transactions()->create([
            'amount' => $amount,
            'order_id' => $order_id,
            'detail' => $detail,
            'type' => $type,
            'from' => auth()->user()->id,
        ]);
    }

    public function servicemanWalletTransactions($request, $serviceman_id)
    {
        try {
            $serviceman = $this->user->findOrFail($serviceman_id);
            $balance = $serviceman->servicemanWallet->balance;
            $transactions = $balance->servicemanWallet->transactions;

            return response()->json([
                '$serviceman_id' => $serviceman?->id,
                'balance' => $balance,
                'transactions' => $transactions,
            ]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
