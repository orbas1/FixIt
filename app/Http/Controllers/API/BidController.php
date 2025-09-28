<?php 

namespace App\Http\Controllers\API;

use Exception;
use App\Enums\RoleEnum;
use App\Models\Bid;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Requests\API\CreateBidRequest;
use App\Http\Requests\API\UpdateBidRequest;
use App\Repositories\API\BidRepository;

class BidController extends Controller
{
    public $repository;

    public function __construct(BidRepository $repository)
    {
        $this->authorizeResource(Bid::class, 'bid');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $bids = $this->repository->whereNull('deleted_at')->where('status','requested');
            $bids = $this->filter($bids, $request);
            return $bids->latest('created_at')->paginate($request->paginate ?? $bids->count());

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Bid $bid)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bid $bid)
    {
       //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateBidRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBidRequest $request, Bid $bid)
    {
        return $this->repository->update($request->all(), $bid->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bid $bid)
    {
        //
    }

    public function filter($bids, $request)
    {
        $roleName = Helpers::getCurrentRoleName();
        if ($request->field && $request->sort) {
            $bids = $bids->orderBy($request->field, $request->sort);
        }

        if ($request->service_request_id) {
            $bids = $bids->where('service_request_id', $request->service_request_id);
        }

        if ($request->start_date && $request->end_date) {
            $bids = $bids->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($roleName == RoleEnum::PROVIDER) {
            $bids = $bids->where('provider_id', Helpers::getCurrentUserId());
        }

        return $bids;
    }

}