<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Security\ZeroTrustAccessEventResource;
use App\Models\ZeroTrustAccessEvent;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ZeroTrustAccessEventController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ZeroTrustAccessEvent::class);

        $user = request()->user();
        $query = ZeroTrustAccessEvent::query()->with(['device', 'networkZone']);

        if (!$user->hasRole('admin') && !$user->can('backend.security.zero_trust.view')) {
            $query->where('user_id', $user->id);
        } elseif ($userId = request('user_id')) {
            $query->where('user_id', $userId);
        }

        $events = $query->latest('created_at')->paginate(request('per_page', 20));

        return ZeroTrustAccessEventResource::collection($events);
    }

    public function show(ZeroTrustAccessEvent $zeroTrustAccessEvent): ZeroTrustAccessEventResource
    {
        $this->authorize('view', $zeroTrustAccessEvent);

        $zeroTrustAccessEvent->loadMissing(['device', 'networkZone']);

        return new ZeroTrustAccessEventResource($zeroTrustAccessEvent);
    }
}
