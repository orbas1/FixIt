<?php

namespace App\Http\Resources\DataGovernance;

use Illuminate\Http\Resources\Json\JsonResource;

class DpiaFindingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'severity' => $this->severity,
            'finding' => $this->finding,
            'recommendation' => $this->recommendation,
            'status' => $this->status,
            'due_at' => optional($this->due_at)->toIso8601String(),
            'mitigated_at' => optional($this->mitigated_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
