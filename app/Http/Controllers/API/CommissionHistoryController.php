<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Enums\RoleEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommissionHistoryResource;
use App\Models\CommissionHistory;
use App\Repositories\API\CommissionHistoryRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CommissionHistoryController extends Controller
{
    public $repository;

    public function __construct(CommissionHistoryRepository $repository)
    {
        $this->authorizeResource(CommissionHistory::class, 'commissionHistory', [
            'except' => 'index', 'show',
        ]);
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // $commisssion = $this->filter($this->repository, $request);
            // return CommissionHistoryResource::collection($commisssion);

            $commission = $this->filter($this->repository, $request);
            return response()->json($commission);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Display the specified resource.
     */
    public function show(CommissionHistory $commissionHistory)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        return $this->repository->store();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CommissionHistory $commissionHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommissionHistory $commissionHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommissionHistory $commissionHistory)
    {
        //
    }

    public function filter($commissionHistories, $request)
    {
        $commission = [];
        $servicemanTotal = 0;
        $roleName = Helpers::getCurrentRoleName();

        if ($request->completed_by_me) {
            $commissionHistories = $commissionHistories->whereHas('serviceman_commissions', function (Builder $query) {
                $query->where('serviceman_id', Helpers::getCurrentUserId());
            });
        }

        if ($roleName == RoleEnum::PROVIDER) {
            $commissionHistories = $commissionHistories->where('provider_id', Helpers::getCurrentUserId());
        }

        if ($roleName == RoleEnum::SERVICEMAN) {
            $commissionHistories = $commissionHistories->whereHas('serviceman_commissions', function (Builder $query) {
                $query->where('serviceman_id', Helpers::getCurrentUserId());
            });
        }

        if ($request->field && $request->sort) {
            $commissionHistories = $commissionHistories->orderBy($request->field, $request->sort);
        }

        if ($request->start_date && $request->end_date) {
            $commissionHistories = $commissionHistories->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        
        $commissionHistories = $commissionHistories->select('id','admin_commission' ,'provider_commission', 'booking_id', 'created_at')->with('booking:id,service_id,booking_number,created_at,total,platform_fees', 'booking.service:id,title', 'serviceman_commissions.serviceman:id,name')->get();
        if($roleName == RoleEnum::PROVIDER){
            $commission['total'] = array_sum($commissionHistories->pluck('provider_commission')->toArray());
        } else {
            foreach ($commissionHistories as $history) {
                foreach ($history->serviceman_commissions as $commission) {
                    if ($commission->serviceman_id == Helpers::getCurrentUserId()) {
                        $servicemanTotal += $commission->commission;
                    }
                }
            }
            $commission['total'] = $servicemanTotal;
        }

        $commission['histories'] = $commission['histories'] = CommissionHistoryResource::collection($commissionHistories);
        return $commission;
    }
}
