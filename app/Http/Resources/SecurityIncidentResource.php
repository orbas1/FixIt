<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SecurityIncidentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->public_id,
            'title' => $this->title,
            'severity' => $this->severity,
            'status' => $this->status,
            'detection_source' => $this->detection_source,
            'impact_summary' => $this->impact_summary,
            'mitigation_steps' => $this->mitigation_steps,
            'root_cause' => $this->root_cause,
            'follow_up_actions' => $this->follow_up_actions ?? [],
            'runbook_updates' => $this->runbook_updates ?? [],
            'impacted_assets' => $this->impacted_assets ?? [],
            'detected_at' => optional($this->detected_at)->toIso8601String(),
            'acknowledged_at' => optional($this->acknowledged_at)->toIso8601String(),
            'resolved_at' => optional($this->resolved_at)->toIso8601String(),
            'timeline' => $this->timeline ?? [],
            'reporter' => $this->reporter?->only(['id', 'name', 'email']),
            'acknowledger' => $this->acknowledger?->only(['id', 'name', 'email']),
            'resolver' => $this->resolver?->only(['id', 'name', 'email']),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
