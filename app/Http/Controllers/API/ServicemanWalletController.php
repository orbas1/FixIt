<?php

namespace App\Http\Controllers\API;

use App\Http\Traits\WalletPointsTrait;
use Exception;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Exceptions\ExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\WalletPointsRequest;
use App\Http\Requests\API\WalletTopUpRequest;
use App\Repositories\API\ServicemanWalletRepository;
use Illuminate\Http\Request;

class ServicemanWalletController extends Controller
{
    use WalletPointsTrait;
    protected $repository;

    public function __construct(ServicemanWalletRepository $repository)
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

    public function topUp(WalletTopUpRequest $request)
    {
        return $this->repository->topUp($request);
    }


    public function filter($servicemanWallet, $request)
    {
        $roleName = Helpers::getCurrentRoleName();
        $serviceman = $request->serviceman_id;
        if ($roleName == RoleEnum::SERVICEMAN) {
            $serviceman = Helpers::getCurrentUserId();
        }

        $servicemanWallet = $this->repository->where('serviceman_id', $serviceman)->first();
        if (!$servicemanWallet) {
            $servicemanWallet = $this->getServicemanWallet($serviceman);
            $servicemanWallet = $servicemanWallet->fresh();
        }

        $transactions = $servicemanWallet->transactions()->where('type', 'LIKE', "%{$request->search}%");
        if ($request->start_date && $request->end_date) {
            $transactions->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $paginate = $request->paginate ?? $servicemanWallet->transactions()->count();
        $servicemanWallet->setRelation('transactions', $transactions->select('amount' , 'type' , 'detail', 'created_at')->simplePaginate($paginate));

        return $servicemanWallet;
    }
}
