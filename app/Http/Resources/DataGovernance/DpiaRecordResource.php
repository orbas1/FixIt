<?php

namespace App\Http\Resources\DataGovernance;

use Illuminate\Http\Resources\Json\JsonResource;

class DpiaRecordResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'ulid' => $this->ulid,
            'status' => $this->status,
            'risk_level' => $this->risk_level,
            'risk_score' => $this->risk_score,
            'assessment_summary' => $this->assessment_summary,
            'mitigation_actions' => $this->mitigation_actions,
            'residual_risks' => $this->residual_risks,
            'submitted_at' => optional($this->submitted_at)->toIso8601String(),
            'approved_at' => optional($this->approved_at)->toIso8601String(),
            'next_review_at' => optional($this->next_review_at)->toIso8601String(),
            'reviewed_by' => $this->reviewed_by,
            'asset' => $this->whenLoaded('asset', fn () => [
                'id' => $this->asset->id,
                'key' => $this->asset->key,
                'name' => $this->asset->name,
                'classification' => $this->asset->classification,
            ]),
            'findings' => DpiaFindingResource::collection($this->whenLoaded('findings')),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
