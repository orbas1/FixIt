<?php

namespace Modules\Subscription\Repositories\Backend;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Subscription\Entities\Plan;
use Prettus\Repository\Eloquent\BaseRepository;

class PlanRepository extends BaseRepository
{
    public function model()
    {
        return Plan::class;
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $this->model->create([
                'name' => $request->name,
                'max_services' => $request->max_services,
                'max_service_packages' => $request->max_service_packages,
                'max_addresses' => $request->max_addresses,
                'max_servicemen' => $request->max_servicemen,
                'price' => $request->price,
                'duration' => $request->duration,
                'description' => $request->description,
                'status' => $request->status,
                'product_id' => $request?->product_id
            ]);

            DB::commit();

            return redirect()->route('backend.plan.index')->with('message', __('static.plan.created_successfully'));

        } catch (Exception $e) {

            DB::rollback();
            throw $e;
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $plan = $this->model->findOrFail($id);

            $plan->update([
                'name' => $request['name'],
                'max_services' => $request['max_services'],
                'max_service_packages' => $request['max_service_packages'],
                'max_addresses' => $request['max_addresses'],
                'max_servicemen' => $request['max_servicemen'],
                'duration' => $request['duration'],
                'description' => $request['description'],
                'price' => $request['price'],
                'status' => $request['status'],
                'product_id' => $request['product_id']
            ]);

            DB::commit();

            return redirect()->route('backend.plan.index')->with('message', __('static.plan.updated_successfully'));
        } catch (Exception $e) {

            DB::rollback();

            throw $e;
        }
    }

    public function status($id, $status)
    {
        try {

            $plan = $this->model->findOrFail($id);
            $plan->update(['status' => $status]);

            return json_encode(['resp' => $plan]);
        } catch (Exception $e) {

            throw $e;
        }
    }

    public function destroy($id)
    {
        try {

            $plan = $this->model->findOrFail($id);
            $plan->destroy($id);

            return redirect()->route('backend.plan.index')->with('message', __('static.plan.deleted_successfully'));
        } catch (Exception $e) {

            throw $e;
        }
    }
}
