<?php

namespace App\Http\Resources\Security;

use Illuminate\Http\Resources\Json\JsonResource;

class TrustedDeviceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->display_name,
            'platform' => $this->platform,
            'trust_level' => $this->trust_level,
            'approved_at' => optional($this->approved_at)->toIso8601String(),
            'revoked_at' => optional($this->revoked_at)->toIso8601String(),
            'last_seen_at' => optional($this->last_seen_at)->toIso8601String(),
            'signals' => $this->signals ?? [],
        ];
    }
}
