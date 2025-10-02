<?php

namespace App\Http\Requests\API\DataGovernance\Dpia;

use App\Models\DpiaRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDpiaRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('dpia_record')) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(['draft', 'in_review', 'mitigation_required', 'approved', 'rejected'])],
            'risk_level' => ['sometimes', 'string', Rule::in(['low', 'medium', 'high'])],
            'risk_score' => ['sometimes', 'integer', 'between:0,100'],
            'assessment_summary' => ['sometimes', 'string'],
            'mitigation_actions' => ['sometimes', 'nullable', 'array'],
            'mitigation_actions.*.action' => ['required_with:mitigation_actions', 'string'],
            'mitigation_actions.*.owner' => ['nullable', 'string'],
            'residual_risks' => ['sometimes', 'nullable', 'array'],
            'residual_risks.*.risk' => ['required_with:residual_risks', 'string'],
            'residual_risks.*.status' => ['nullable', 'string'],
            'submitted_at' => ['sometimes', 'nullable', 'date'],
            'approved_at' => ['sometimes', 'nullable', 'date'],
            'reviewed_by' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'next_review_at' => ['sometimes', 'nullable', 'date'],
            'findings' => ['sometimes', 'array'],
            'findings.*.id' => ['nullable', 'integer', 'exists:dpia_findings,id'],
            'findings.*.category' => ['required', 'string'],
            'findings.*.severity' => ['required', 'string', Rule::in(['low', 'medium', 'high', 'critical'])],
            'findings.*.finding' => ['required', 'string'],
            'findings.*.recommendation' => ['nullable', 'string'],
            'findings.*.status' => ['nullable', 'string', Rule::in(['open', 'in_progress', 'mitigated'])],
            'findings.*.due_at' => ['nullable', 'date'],
            'findings.*.mitigated_at' => ['nullable', 'date'],
        ];
    }
}
