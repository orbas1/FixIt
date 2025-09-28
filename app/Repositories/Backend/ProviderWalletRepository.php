<?php

namespace App\Repositories\Backend;

use App\Enums\RoleEnum;
use App\Enums\TransactionType;
use App\Enums\WalletPointsDetail;
use App\Helpers\Helpers;
use App\Models\ProviderWallet;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class ProviderWalletRepository extends BaseRepository
{
    protected $user;

    public function model()
    {
        $this->user = new User();

        return ProviderWallet::class;
    }

    public function index($dataTable)
    {
        $providers = $this->user->role(RoleEnum::PROVIDER)->with('media')->get();

        $provider = $this->model->where('provider_id', request()->provider_id)->first();

        return $dataTable->render('backend.provider-wallet.index', ['providers' => $providers, 'balance' => $provider->balance ?? null]);
    }

    public function getRoleId()
    {
        $roleName = Helpers::getCurrentRoleName() ?? RoleEnum::ADMIN;
        if ($roleName == RoleEnum::ADMIN) {
            return $this->user->role(RoleEnum::ADMIN)->first()->id;
        }

        return Helpers::getCurrentUserId();
    }

    public function creditOrdebit($request)
    {
        try {
            if ($request->consumer_id) {
                if ($request->type == 'credit') {
                
                    $wallet = $this->creditWallet($request->consumer_id, $request->balance, $request->note ?? WalletPointsDetail::ADMIN_CREDIT);
                    if ($wallet) {
                        $wallet->setRelation('transactions', $wallet->transactions()->paginate($request->paginate ?? $wallet->transactions()->count()));
                    }

                    return redirect()->back()->with('message', 'Credited Successfully');
                } else {

                    return $wallet = $this->debitWallet($request->consumer_id, $request->balance, $request->note ?? WalletPointsDetail::ADMIN_DEBIT, $request);
                }
            } else {
                return back()->with('error', 'Please select provider first');
            }
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function getWallet($consumer_id)
    {
        $roleName = Helpers::getRoleByUserId($consumer_id);
        if ($roleName == RoleEnum::PROVIDER) {
            return $this->model->firstOrCreate(['provider_id' => $consumer_id]);
        }

        return back()->with('error', 'user must be Provider');
    }

    public function creditTransaction($model, $amount, $detail, $booking_id = null)
    {
        return $this->storeTransaction($model, TransactionType::CREDIT, $detail, $amount, $booking_id);
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

    public function debitWallet($consumer_id, $balance, $detail, $request)
    {
        $wallet = $this->getWallet($consumer_id);
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

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $wallet = $this->model->create([
                'name' => $request->name,
                'rate' => $request->rate,
                'status' => $request->status,
            ]);

            DB::commit();

            return redirect()->route('backend.wallet.index')->with('message', 'Wallet Created Successfully.');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($wallet)
    {
        return view('backend.wallet.edit', ['wallet' => $wallet]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $wallet = $this->model->findOrFail($id);
            $wallet->update($request->all());

            DB::commit();

            return redirect()->route('backend.wallet.index')->with('success', 'Wallet Updated Successfully.');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $wallet = $this->model->findOrFail($id);
            $wallet->destroy($id);

            DB::commit();

            return redirect()->back()->with(['message' => 'Wallet deleted successfully']);
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus($request)
    {
        try {
            $wallet = $this->model->findOrFail($request->userId);
            $wallet->update([
                'status' => $request->status,
            ]);

            return response()->json(['message' => 'Status updated successfully']);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteAll($ids)
    {
        DB::beginTransaction();
        try {

            $this->model->whereNot('system_reserve', true)->whereIn('id', $ids)->delete();

            return back()->with('message', 'Roles Deleted Successfully');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function getProvidertransations($request, $provider_id)
    {
        try {
            $provider = $this->user->findOrFail($provider_id);
            $balance = $provider->providerWallet->balance;
            $transactions = $provider->providerWallet->transactions;

            return response()->json([
                '$provider_id' => $provider->id,
                'balance' => $balance,
                'transactions' => $transactions,
            ]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
