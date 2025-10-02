<?php

namespace App\Http\Resources\Security;

use Illuminate\Http\Resources\Json\JsonResource;

class ZeroTrustAccessEventResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'decision' => $this->decision,
            'risk_score' => $this->risk_score,
            'signals' => $this->signals ?? [],
            'policy_version' => $this->policy_version,
            'request_ip' => $this->request_ip,
            'user_agent' => $this->user_agent,
            'mfa_verified_at' => optional($this->mfa_verified_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'enforced_controls' => $this->enforced_controls ?? [],
            'device' => $this->whenLoaded('device', fn () => new TrustedDeviceResource($this->device)),
            'network_zone' => $this->whenLoaded('networkZone', fn () => new NetworkZoneResource($this->networkZone)),
        ];
    }
}
