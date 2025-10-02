<?php

namespace App\Http\Requests\API\DataGovernance\DataAsset;

use App\Models\DataAsset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDataAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', DataAsset::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'data_domain_id' => ['nullable', 'integer', 'exists:data_domains,id'],
            'steward_id' => ['nullable', 'integer', 'exists:users,id'],
            'key' => ['required', 'string', 'max:120', 'unique:data_assets,key'],
            'name' => ['required', 'string', 'max:255'],
            'classification' => ['required', 'string', Rule::in(config('data_governance.classifications'))],
            'processing_purpose' => ['required', 'string'],
            'data_elements' => ['required', 'array', 'min:1'],
            'data_elements.*' => ['string', 'max:120'],
            'lawful_bases' => ['required', 'array', 'min:1'],
            'lawful_bases.*' => ['string', Rule::in(config('data_governance.lawful_bases'))],
            'retention_period_days' => ['nullable', 'integer', 'min:1'],
            'requires_dpia' => ['boolean'],
            'residency_exceptions' => ['nullable', 'array'],
            'monitoring_controls' => ['nullable', 'array'],
            'next_review_at' => ['nullable', 'date'],
            'residency_policies' => ['nullable', 'array', 'min:1'],
            'residency_policies.*.zone_code' => ['required_with:residency_policies', 'string', 'exists:data_residency_zones,code'],
            'residency_policies.*.storage_role' => ['required_with:residency_policies', Rule::in(['primary', 'replica', 'processing'])],
            'residency_policies.*.lawful_basis' => ['required_with:residency_policies', Rule::in(config('data_governance.lawful_bases'))],
            'residency_policies.*.encryption_profile' => ['required_with:residency_policies', 'string', Rule::in(config('data_governance.residency_policies.encryption_profiles'))],
            'residency_policies.*.data_controller' => ['required_with:residency_policies', 'string', 'max:255'],
            'residency_policies.*.cross_border_allowed' => ['sometimes', 'boolean'],
            'residency_policies.*.transfer_safeguards' => ['nullable', 'array'],
            'residency_policies.*.audit_controls' => ['nullable', 'array'],
        ];
    }
}
