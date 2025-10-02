<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Security\TrustedDevice\UpdateTrustedDeviceRequest;
use App\Http\Resources\Security\TrustedDeviceResource;
use App\Models\TrustedDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TrustedDeviceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', TrustedDevice::class);

        $query = TrustedDevice::query();
        $user = request()->user();

        if (!$user->hasRole('admin') && !$user->can('backend.security.zero_trust.view')) {
            $query->where('user_id', $user->id);
        } elseif ($userId = request('user_id')) {
            $query->where('user_id', $userId);
        }

        $devices = $query
            ->latest('last_seen_at')
            ->latest()
            ->paginate(request('per_page', 15));

        return TrustedDeviceResource::collection($devices);
    }

    public function update(UpdateTrustedDeviceRequest $request, TrustedDevice $trustedDevice): TrustedDeviceResource
    {
        $trustedDevice->fill($request->only(['display_name']));

        if ($request->filled('trust_level')) {
            $trustLevel = $request->string('trust_level')->toString();
            if ($trustLevel === 'trusted') {
                $trustedDevice->approve('manual_override');
            } elseif ($trustLevel === 'revoked') {
                $trustedDevice->revoke();
            } else {
                $trustedDevice->forceFill(['trust_level' => $trustLevel])->save();
            }
        }

        $trustedDevice->save();

        return new TrustedDeviceResource($trustedDevice->refresh());
    }

    public function destroy(TrustedDevice $trustedDevice): JsonResponse
    {
        $this->authorize('delete', $trustedDevice);

        $trustedDevice->revoke();

        return response()->json(null, 204);
    }
}
