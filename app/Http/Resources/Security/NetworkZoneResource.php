<?php

namespace App\Http\Resources\Security;

use Illuminate\Http\Resources\Json\JsonResource;

class NetworkZoneResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'type' => $this->type,
            'risk_level' => $this->risk_level,
            'ip_ranges' => $this->normalizedIpRanges(),
            'device_tags' => $this->device_tags ?? [],
            'enforced_controls' => $this->enforced_controls ?? [],
            'is_active' => $this->is_active,
            'description' => $this->description,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
