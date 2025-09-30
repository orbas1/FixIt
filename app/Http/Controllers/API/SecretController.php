<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Security\RotateSecretRequest;
use App\Http\Requests\API\Security\StoreSecretRequest;
use App\Models\Secret;
use App\Services\Security\SecretVaultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class SecretController extends Controller
{
    public function __construct(private readonly SecretVaultService $secretVaultService)
    {
    }

    public function index(): JsonResource
    {
        Gate::authorize('viewAny', Secret::class);

        $secrets = Secret::query()
            ->orderByDesc('updated_at')
            ->paginate(25);

        return JsonResource::collection($secrets);
    }

    public function store(StoreSecretRequest $request): JsonResource
    {
        $secret = $this->secretVaultService->put(
            (string) $request->input('key'),
            $request->input('value'),
            $request->input('metadata', [])
        );

        return new JsonResource($secret);
    }

    public function show(Secret $secret): JsonResource
    {
        Gate::authorize('view', $secret);

        $value = $this->secretVaultService->get($secret->key);

        return JsonResource::make([
            'id' => $secret->id,
            'key' => $secret->key,
            'version' => $secret->version,
            'metadata' => $secret->metadata,
            'rotated_at' => $secret->rotated_at,
            'value' => $value,
        ]);
    }

    public function update(RotateSecretRequest $request, Secret $secret): JsonResource
    {
        $secret = $this->secretVaultService->rotate(
            $secret->key,
            $request->input('value'),
            $request->input('metadata', $secret->metadata)
        );

        return new JsonResource($secret);
    }

    public function destroy(Secret $secret): JsonResponse
    {
        Gate::authorize('delete', $secret);

        $this->secretVaultService->forget($secret->key);

        return response()->json(['status' => 'deleted']);
    }
}
