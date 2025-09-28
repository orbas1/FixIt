<?php

namespace Modules\Subscription\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Subscription\DataTables\PlanDataTable;
use Modules\Subscription\DataTables\SubscriptionDataTable;
use Modules\Subscription\Entities\Plan;
use Modules\Subscription\Http\Requests\Backend\CreatePlanRequest;
use Modules\Subscription\Http\Requests\Backend\UpdatePlanRequest;
use Modules\Subscription\Repositories\Backend\PlanRepository;

class PlanController extends Controller
{
    protected $repository;

    public function __construct(PlanRepository $repository)
    {
        $this->authorizeResource(Plan::class, 'plan');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(PlanDataTable $dataTable)
    {
        return $dataTable->render('subscription::index');
    }

    /**
     * Display a listing of the resource.
     */
    public function subscription(SubscriptionDataTable $dataTable)
    {
        return $dataTable->render('subscription::subscription');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('subscription::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePlanRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('subscription::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plan $plan)
    {
        $plan = $this->repository->find($plan->id);
        return view('subscription::edit', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        return $this->repository->update($request->all(), $plan->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan)
    {
        return $this->repository->destroy($plan->id);
    }

    public function toggleStatus(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $plan = Plan::find($request->id[$row]);
                $plan->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
