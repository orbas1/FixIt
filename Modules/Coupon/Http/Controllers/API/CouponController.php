<?php

namespace Modules\Coupon\Http\Controllers\API;

use App\Http\Resources\UserDashboardCouponResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coupon\Entities\Coupon;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $model;

    public function __construct(Coupon $coupon)
    {
        $this->model = $coupon;
    }

    public function index(Request $request)
    {
       $coupon = $this->model->where('status', true)
        ->where('is_expired', 0)
        ->where(function ($query) {
            $query->whereNull('end_date')
                  ->orWhere('end_date', '>', now());
        });

        if ($request->has('zone_ids')) {
            $zoneIds = is_array($request->zone_ids)
                ? $request->zone_ids
                : explode(',', $request->zone_ids);

            $coupon->whereHas('zones', function ($query) use ($zoneIds) {
                $query->whereIn('zones.id', $zoneIds);
            });
        }

        $data = $coupon->latest('created_at')
            ->simplePaginate($request->paginate ?? $coupon->count());

        return UserDashboardCouponResource::collection($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
