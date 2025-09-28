<?php

namespace Modules\Subscription\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Subscription\Entities\Plan;
use Modules\Subscription\Http\Requests\API\PurchasePlanRequest;
use Modules\Subscription\Repositories\API\SubscriptionRepository;

class SubscriptionController extends Controller
{
    protected $repository;

    public function __construct(SubscriptionRepository $repository)
    {
        $this->authorizeResource(Plan::class, 'plan', [
            'except' => 'index',
        ]);
        $this->repository = $repository;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function purchase(PurchasePlanRequest $request)
    {
        return $this->repository->purchase($request);
    }

    public function getPlans(Request $request)
    {
        return $this->repository->getPlans($request);
    }

    public function getPlansProductIds(Request $request)
    {
        return $this->repository->getPlansProductIds($request);
    }
}
