<?php

namespace App\Http\Controllers\API;

use App\Enums\RoleEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreditDebitWalletRequest;
use App\Http\Requests\API\WalletTopUpRequest;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;
use App\Repositories\API\WalletRepository;
use Exception;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public $repository;

    public function __construct(WalletRepository $repository)
    {
        if (Helpers::walletIsEnable()) {
            return $this->repository = $repository;
        }

        throw new ExceptionHandler(__('static.wallet.disabled'), 400);
    }

    /**
     * Display a Consumer Wallet Transactions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {

            return $this->filter($this->repository, $request);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Credit Balance from Consumer Wallet.
     *
     * @return \Illuminate\Http\Response
     */
    public function credit(CreditDebitWalletRequest $request)
    {
        return $this->repository->credit($request);
    }

    /**
     * Debit Balance from Consumer Wallet.
     *
     * @return \Illuminate\Http\Response
     */
    public function debit(CreditDebitWalletRequest $request)
    {
        return $this->repository->debit($request);
    }

    public function topUp(WalletTopUpRequest $request)
    {
        return $this->repository->topUp($request);
    }

    public function filter($wallet, $request)
    {
        $consumer_id = $request->consumer_id ?? auth()->user()->id;
        $wallet = $wallet->where('consumer_id', $consumer_id)->first();

        if (!$wallet) {
            $wallet = $this->getWallet($request->consumer_id);
            $wallet = $wallet->fresh();
        }

        // $transactions = $wallet->transactions()->where('type', 'LIKE', "%{$request->search}%");
        $transactions = $wallet->transactions();
        // if ($request->start_date && $request->end_date) {
        //     $transactions->whereBetween('created_at', [$request->start_date, $request->end_date]);
        // }

        $paginate = $request->paginate ?? $wallet->transactions()->count();
        $wallet->setRelation('transactions', $transactions->SimplePaginate($paginate));

        return new WalletResource($wallet);
    }

    public function getWallet($consumer_id)
    {
        $roleName = Helpers::getRoleByUserId($consumer_id);
        if ($roleName == RoleEnum::CONSUMER) {
            return Wallet::firstOrCreate(['consumer_id' => $consumer_id]);
        }

        throw new ExceptionHandler('user must be '.RoleEnum::CONSUMER, 400);
    }
}
