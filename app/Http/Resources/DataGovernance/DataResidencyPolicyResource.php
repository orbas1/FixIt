<?php

namespace App\Http\Resources\DataGovernance;

use Illuminate\Http\Resources\Json\JsonResource;

class DataResidencyPolicyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'storage_role' => $this->storage_role,
            'lawful_basis' => $this->lawful_basis,
            'encryption_profile' => $this->encryption_profile,
            'data_controller' => $this->data_controller,
            'cross_border_allowed' => $this->cross_border_allowed,
            'transfer_safeguards' => $this->transfer_safeguards,
            'audit_controls' => $this->audit_controls,
            'zone' => new DataResidencyZoneResource($this->whenLoaded('zone')),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
