<?php

namespace App\Http\Resources\DataGovernance;

use Illuminate\Http\Resources\Json\JsonResource;

class DataResidencyZoneResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'ulid' => $this->ulid,
            'code' => $this->code,
            'name' => $this->name,
            'region' => $this->region,
            'country_codes' => $this->country_codes,
            'default_controller' => $this->default_controller,
            'approved_services' => $this->approved_services,
            'risk_rating' => $this->risk_rating,
            'is_active' => $this->is_active,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
