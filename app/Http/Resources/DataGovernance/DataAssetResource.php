<?php

namespace App\Http\Resources\DataGovernance;

use Illuminate\Http\Resources\Json\JsonResource;

class DataAssetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'ulid' => $this->ulid,
            'key' => $this->key,
            'name' => $this->name,
            'classification' => $this->classification,
            'processing_purpose' => $this->processing_purpose,
            'data_elements' => $this->data_elements,
            'lawful_bases' => $this->lawful_bases,
            'retention_period_days' => $this->retention_period_days,
            'requires_dpia' => $this->requires_dpia,
            'residency_exceptions' => $this->residency_exceptions,
            'monitoring_controls' => $this->monitoring_controls,
            'next_review_at' => optional($this->next_review_at)->toIso8601String(),
            'domain' => $this->whenLoaded('domain', fn () => [
                'id' => $this->domain->id,
                'name' => $this->domain->name,
                'slug' => $this->domain->slug,
            ]),
            'steward' => $this->whenLoaded('steward', fn () => [
                'id' => $this->steward->id,
                'name' => $this->steward->name,
                'email' => $this->steward->email,
            ]),
            'residency_policies' => DataResidencyPolicyResource::collection($this->whenLoaded('residencyPolicies')),
            'dpia_records' => DpiaRecordResource::collection($this->whenLoaded('dpiaRecords')),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'compliance' => $this->when(isset($this->compliance), $this->compliance),
        ];
    }
}
