<?php

namespace Modules\Coupon\Repositories;

use App\Enums\RoleEnum;
use App\Models\Service;
use App\Models\User;
use App\Models\Zone;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Coupon\Entities\Coupon;
use Prettus\Repository\Eloquent\BaseRepository;

class CouponRepository extends BaseRepository
{
    protected $service;
    protected $user;
    protected $zone;

    public function model()
    {
        $this->service = new Service();
        $this->user = new User();
        $this->zone = new Zone();

        return Coupon::class;
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $start_date = null;
            $end_date = null;
            if ($request->is_expired == 1) {
                [$start_date, $end_date] = explode(' to ', $request->start_end_date);
                $start_date = DateTime::createFromFormat('d-m-Y', $start_date)->format('Y-m-d');
                $end_date = DateTime::createFromFormat('d-m-Y', $end_date)->format('Y-m-d');
            }

            $coupon = $this->model->create([
                'code' => $request->code,
                'title' => $request->title,
                'type' => $request->type,
                'amount' => $request->amount ?? $request->percentage_amount,
                'min_spend' => $request->min_spend,
                'is_unlimited' => $request->is_unlimited,
                'usage_per_coupon' => $request->usage_per_coupon,
                'usage_per_customer' => $request->usage_per_customer,
                'status' => $request->status,
                'is_expired' => $request->is_expired,
                'is_apply_all' => $request->is_apply_all,
                'is_first_order' => $request->is_first_order,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]);

            if (isset($request->services)) {
                $coupon->services()->attach($request->services);
                $coupon->services;
            }

            if (isset($request->users)) {
                $coupon->users()->attach($request->users);
                $coupon->users;
            }

            if (isset($request->zones)) {
                $coupon->zones()->attach($request->zones);
                $coupon->zones;
            }

            if (isset($request->exclude_services)) {
                $coupon->exclude_services()->attach($request->exclude_services);
                $coupon->exclude_services;
            }
            DB::commit();

            return redirect()->route('backend.coupon.index')->with('message', 'Coupon Created Successfully.');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            if ($request->is_expired == 1) {
                [$date, $till_date] = explode(' to ', $request->start_end_date);
            }
            $coupon = $this->model->findOrFail($id);
            if ($request->is_expired == false) {
                $start_date = null;
                $end_date = null;
            } else {
                $start_date = Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
                $end_date = Carbon::createFromFormat('d/m/Y', $till_date)->format('Y-m-d');
            }
            if ($request->is_unlimited == true) {
                $perCoupon = null;
                $usagePerCoustomer = null;
            } else {
                $perCoupon = $request->usage_per_coupon;
                $usagePerCoustomer = $request->usage_per_customer;
            }
            if ($request->type == 'free_service') {
                $amount = null;
            } else {
                $amount = $request->amount ?? $request->percentage_amount;
            }

            if (isset($request->zones)) {
                $coupon->zones()->sync([]);
                $coupon->zones()->sync($request->zones);
            }

            $coupon->update([
                'title' => $request->title,
                'code' => $request->code,
                'type' => $request->type,
                'amount' => $amount,
                'min_spend' => $request->min_spend,
                'is_unlimited' => $request->is_unlimited,
                'usage_per_coupon' => $perCoupon,
                'usage_per_customer' => $usagePerCoustomer,
                'status' => $request->status,
                'is_expired' => $request->is_expired,
                'is_apply_all' => $request->is_apply_all,
                'is_first_order' => $request->is_first_order,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]);


            if (! $request['is_apply_all']) {
                $coupon->exclude_services()->sync([]);
                $coupon->services()->sync($request->services);
            }

            if ($request['is_apply_all']) {
                $coupon->services()->sync([]);
                $coupon->exclude_services()->sync($request->exclude_services);
            }

            DB::commit();

            return redirect()->route('backend.coupon.index')->with('message', 'Coupon Updated Successfully.');
        } catch (Exception $e) {

            DB::rollback();
            return $e;
            return back()->with('error', $e->getMessage());
        }
    }
    public function status($id, $status)
    {
        try {
            $coupon = $this->model->findOrFail($id);
            $coupon->update(['status' => $status]);

            return json_encode(['resp' => $coupon]);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $coupon = $this->model->findOrFail($id);
        $services = $this->service->get();
        $coupon = $this->model->where('id', $coupon->id)->first();
        $users = $this->user->where('status', true)->role(RoleEnum::CONSUMER)->get();
        $zones = $this->zone->where('status', true)?->get(['id', 'name']);
        $default_services = $this->getDefualtCouponServices($coupon);
        $default_users = $this->getDefualtCouponUsers($coupon);
        $default_zones = $this->getDefualtCouponZones($coupon);
        $exclude_services = $this->getDefualtCouponExcludeServices($coupon);

        return view('coupon::edit',[
                'services' => $services,
                'coupon' => $coupon,
                'users' => $users,
                'zones' => $zones,
                'default_services' => $default_services,
                'exclude_services' => $exclude_services,
                'default_users' => $default_users,
                'default_zones' => $default_zones
            ]);
    }

    private function getDefualtCouponServices($coupon)
    {
        $services = [];
        foreach ($coupon->services as $service) {
            $services[] = $service->id;
        }
        $services = array_map('strval', $services);

        return $services;
    }

    private function getDefualtCouponUsers($coupon)
    {
        $users = [];
        foreach ($coupon->users as $user) {
            $users[] = $user->id;
        }
        $users = array_map('strval', $users);

        return $users;
    }

    private function getDefualtCouponZones($coupon)
    {
        $zones = [];
        foreach ($coupon->zones as $zone) {
            $zones[] = $zone->id;
        }
        $zones = array_map('strval', $zones);

        return $zones;
    }

    private function getDefualtCouponExcludeServices($coupon)
    {
        $services = [];
        foreach ($coupon->exclude_services as $service) {
            $services[] = $service->id;
        }
        $services = array_map('strval', $services);

        return $services;
    }

    public function destroy($id)
    {
        try {

            $coupon = $this->model->findOrFail($id);
            $coupon->destroy($id);

            return redirect()->route('backend.coupon.index')->with('message', 'Coupon Deleted Successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
