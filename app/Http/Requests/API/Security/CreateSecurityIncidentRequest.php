<?php

namespace App\Http\Requests\API\Security;

use App\Enums\SecurityIncidentSeverity;
use App\Models\SecurityIncident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSecurityIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SecurityIncident::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'severity' => ['nullable', 'string', Rule::in(SecurityIncidentSeverity::values())],
            'detection_source' => ['nullable', 'string', 'max:255'],
            'impacted_assets' => ['nullable', 'array'],
            'impacted_assets.*' => ['string', 'max:255'],
            'impact_summary' => ['nullable', 'string'],
            'mitigation_steps' => ['nullable', 'string'],
            'follow_up_actions' => ['nullable', 'array'],
            'follow_up_actions.*.owner' => ['required_with:follow_up_actions', 'string', 'max:255'],
            'follow_up_actions.*.action' => ['required_with:follow_up_actions', 'string', 'max:500'],
            'follow_up_actions.*.due_at' => ['nullable', 'date'],
            'runbook_updates' => ['nullable', 'array'],
            'runbook_updates.*.section' => ['required_with:runbook_updates', 'string', 'max:255'],
            'runbook_updates.*.change_log' => ['required_with:runbook_updates', 'string'],
            'detected_at' => ['nullable', 'date'],
        ];
    }
}
