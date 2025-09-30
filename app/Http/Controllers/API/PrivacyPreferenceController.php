<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Security\UpdatePrivacyPreferencesRequest;
use App\Services\Security\PrivacyComplianceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivacyPreferenceController extends Controller
{
    public function __construct(private readonly PrivacyComplianceService $privacyComplianceService)
    {
    }

    public function show(): JsonResource
    {
        $preferences = $this->privacyComplianceService->getPreferences(request()->user());

        return JsonResource::make([
            'preferences' => $preferences,
        ]);
    }

    public function update(UpdatePrivacyPreferencesRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->privacyComplianceService->updatePreferences($user, $request->input('preferences'));

        return response()->json([
            'preferences' => $this->privacyComplianceService->getPreferences($user),
        ]);
    }

    public function export(): JsonResponse
    {
        $user = request()->user();
        $token = $this->privacyComplianceService->requestDataExport($user);

        return response()->json(['status' => 'queued', 'token' => $token]);
    }

    public function destroy(): JsonResponse
    {
        $user = request()->user();
        $token = $this->privacyComplianceService->requestDataDeletion($user);

        return response()->json(['status' => 'queued', 'token' => $token]);
    }
}
