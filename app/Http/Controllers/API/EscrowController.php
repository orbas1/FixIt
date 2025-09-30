<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Escrow\CreatePaymentIntentRequest;
use App\Http\Requests\API\Escrow\EscrowRefundRequest;
use App\Http\Requests\API\Escrow\EscrowReleaseRequest;
use App\Http\Requests\API\Escrow\EscrowStoreRequest;
use App\Http\Resources\EscrowResource;
use App\Models\Escrow;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Escrow\EscrowService;
use App\Services\Payments\EscrowPaymentIntentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class EscrowController extends Controller
{
    public function __construct(
        private readonly EscrowService $escrowService,
        private readonly EscrowPaymentIntentService $paymentIntents,
    ) {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $role = $request->query('role', 'consumer');

        $query = Escrow::query()->with(['transactions' => function ($builder) {
            $builder->orderByDesc('occurred_at');
        }]);

        if ($role === 'provider') {
            $query->where('provider_id', $user->getKey());
        } else {
            $query->where('consumer_id', $user->getKey());
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $escrows = $query->latest('created_at')->paginate($request->integer('per_page', 15));

        return EscrowResource::collection($escrows);
    }

    public function store(EscrowStoreRequest $request): JsonResponse
    {
        $consumer = $request->user();
        $this->authorize('create', [Escrow::class, $consumer]);

        $serviceRequest = ServiceRequest::query()->findOrFail($request->integer('service_request_id'));
        $provider = User::query()->findOrFail($request->integer('provider_id'));

        $escrow = $this->escrowService->initialize(
            $serviceRequest,
            $consumer,
            $provider,
            (float) $request->input('amount'),
            strtoupper((string) $request->input('currency')),
            ['initiated_via' => 'api']
        )->load('transactions');

        return (new EscrowResource($escrow))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Escrow $escrow): EscrowResource
    {
        $this->authorize('view', $escrow);

        return new EscrowResource($escrow->load('transactions'));
    }

    public function release(EscrowReleaseRequest $request, Escrow $escrow): EscrowResource
    {
        $this->authorize('release', $escrow);

        $payload = $request->validated();
        $escrow = $this->escrowService->release(
            $escrow,
            (float) $payload['amount'],
            $request->user(),
            Arr::except($payload, ['amount'])
        )->load('transactions');

        return new EscrowResource($escrow);
    }

    public function refund(EscrowRefundRequest $request, Escrow $escrow): EscrowResource
    {
        $this->authorize('refund', $escrow);

        $payload = $request->validated();
        $escrow = $this->escrowService->refund(
            $escrow,
            (float) $payload['amount'],
            $request->user(),
            Arr::except($payload, ['amount'])
        )->load('transactions');

        return new EscrowResource($escrow);
    }

    public function paymentIntent(CreatePaymentIntentRequest $request, Escrow $escrow): JsonResponse
    {
        $this->authorize('fund', $escrow);

        $intent = $this->paymentIntents->createPaymentIntent($escrow, $request->user(), $request->context());

        return response()->json(['data' => $intent]);
    }
}
