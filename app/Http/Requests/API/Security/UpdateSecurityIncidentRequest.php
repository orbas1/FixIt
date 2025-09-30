<?php

namespace App\Http\Requests\API\Security;

use App\Enums\SecurityIncidentSeverity;
use App\Models\SecurityIncident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSecurityIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SecurityIncident|null $incident */
        $incident = $this->route('incident');

        return $incident && $this->user()?->can('update', $incident);
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'severity' => ['sometimes', 'string', Rule::in(SecurityIncidentSeverity::values())],
            'detection_source' => ['sometimes', 'nullable', 'string', 'max:255'],
            'impacted_assets' => ['sometimes', 'nullable', 'array'],
            'impacted_assets.*' => ['string', 'max:255'],
            'impact_summary' => ['sometimes', 'nullable', 'string'],
            'mitigation_steps' => ['sometimes', 'nullable', 'string'],
            'follow_up_actions' => ['sometimes', 'nullable', 'array'],
            'follow_up_actions.*.owner' => ['required_with:follow_up_actions', 'string', 'max:255'],
            'follow_up_actions.*.action' => ['required_with:follow_up_actions', 'string', 'max:500'],
            'follow_up_actions.*.due_at' => ['nullable', 'date'],
            'runbook_updates' => ['sometimes', 'nullable', 'array'],
            'runbook_updates.*.section' => ['required_with:runbook_updates', 'string', 'max:255'],
            'runbook_updates.*.change_log' => ['required_with:runbook_updates', 'string'],
        ];
    }
}
