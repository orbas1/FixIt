<?php

namespace App\Http\Requests\API\Security;

use App\Models\SecurityIncident;
use Illuminate\Foundation\Http\FormRequest;

class ResolveSecurityIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SecurityIncident|null $incident */
        $incident = $this->route('incident');

        return $incident && $this->user()?->can('resolve', $incident);
    }

    public function rules(): array
    {
        return [
            'root_cause' => ['required', 'string'],
            'impact_summary' => ['required', 'string'],
            'mitigation_steps' => ['required', 'string'],
            'follow_up_actions' => ['nullable', 'array'],
            'follow_up_actions.*.owner' => ['required_with:follow_up_actions', 'string', 'max:255'],
            'follow_up_actions.*.action' => ['required_with:follow_up_actions', 'string', 'max:500'],
            'follow_up_actions.*.due_at' => ['nullable', 'date'],
            'runbook_updates' => ['nullable', 'array'],
            'runbook_updates.*.section' => ['required_with:runbook_updates', 'string', 'max:255'],
            'runbook_updates.*.change_log' => ['required_with:runbook_updates', 'string'],
        ];
    }
}
