<?php

namespace App\Http\Requests\API\DataGovernance\DataAsset;

use App\Models\DataAsset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDataAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('data_asset')) ?? false;
    }

    public function rules(): array
    {
        /** @var DataAsset $asset */
        $asset = $this->route('data_asset');

        return [
            'data_domain_id' => ['sometimes', 'nullable', 'integer', 'exists:data_domains,id'],
            'steward_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'key' => ['sometimes', 'string', 'max:120', Rule::unique('data_assets', 'key')->ignore($asset?->getKey())],
            'name' => ['sometimes', 'string', 'max:255'],
            'classification' => ['sometimes', 'string', Rule::in(config('data_governance.classifications'))],
            'processing_purpose' => ['sometimes', 'string'],
            'data_elements' => ['sometimes', 'array', 'min:1'],
            'data_elements.*' => ['string', 'max:120'],
            'lawful_bases' => ['sometimes', 'array', 'min:1'],
            'lawful_bases.*' => ['string', Rule::in(config('data_governance.lawful_bases'))],
            'retention_period_days' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'requires_dpia' => ['sometimes', 'boolean'],
            'residency_exceptions' => ['sometimes', 'nullable', 'array'],
            'monitoring_controls' => ['sometimes', 'nullable', 'array'],
            'next_review_at' => ['sometimes', 'nullable', 'date'],
            'residency_policies' => ['sometimes', 'nullable', 'array'],
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
