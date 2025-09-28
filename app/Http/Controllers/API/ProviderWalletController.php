<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Enums\RoleEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreditDebitProviderWalletRequest;
use App\Http\Requests\API\WalletPointsRequest;
use App\Http\Requests\API\WalletTopUpRequest;
use App\Http\Traits\WalletPointsTrait;
use App\Repositories\API\ProviderWalletRepository;
use Illuminate\Http\Request;

class ProviderWalletController extends Controller
{
    use WalletPointsTrait;

    protected $repository;

    public function __construct(ProviderWalletRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a Vendor Wallet Transactions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(WalletPointsRequest $request)
    {
        try {
            return $this->filter($this->repository, $request);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Credit Balance from Vendor Wallet.
     *
     * @return \Illuminate\Http\Response
     */
    public function credit(CreditDebitProviderWalletRequest $request)
    {
        return $this->repository->credit($request);
    }

    /**
     * Debit Balance from Vendor Wallet.
     *
     * @return \Illuminate\Http\Response
     */
    public function debit(CreditDebitProviderWalletRequest $request)
    {
        return $this->repository->debit($request);
    }

    public function filter($providerWallet, $request)
    {
        $roleName = Helpers::getCurrentRoleName();
        $provider = $request->provider_id;
        if ($roleName == RoleEnum::PROVIDER) {
            $provider = Helpers::getCurrentUserId();
        }

        $providerWallet = $this->repository->where('provider_id', $provider)->first();
        if (! $providerWallet) {
            $providerWallet = $this->getProviderWallet($provider);
            $providerWallet = $providerWallet->fresh();
        }

        $transactions = $providerWallet->transactions()->where('type', 'LIKE', "%{$request->search}%");
        if ($request->start_date && $request->end_date) {
            $transactions->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $paginate = $request->paginate ?? $providerWallet->transactions()->count();
        $providerWallet->setRelation('transactions', $transactions->select('amount' , 'type' , 'detail', 'created_at')->simplePaginate($paginate));

        return $providerWallet;
    }

    public function topUp(WalletTopUpRequest $request)
    {
        return $this->repository->topUp($request);
    }

    public function withdrawRequest(Request $request)
    {
        return $this->repository->withdrawRequest($request);
    }
}
