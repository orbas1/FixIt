<?php

namespace App\Http\Requests\API\DataGovernance\DataResidencyZone;

use App\Models\DataResidencyZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDataResidencyZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('data_residency_zone')) ?? false;
    }

    public function rules(): array
    {
        /** @var DataResidencyZone $zone */
        $zone = $this->route('data_residency_zone');

        return [
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('data_residency_zones', 'code')->ignore($zone?->getKey())],
            'name' => ['sometimes', 'string', 'max:255'],
            'region' => ['sometimes', 'string', Rule::in(array_keys(config('data_governance.residency_policies.regions')))],
            'country_codes' => ['sometimes', 'array', 'min:1'],
            'country_codes.*' => ['string', 'size:2'],
            'default_controller' => ['sometimes', 'nullable', 'string', 'max:255'],
            'approved_services' => ['sometimes', 'nullable', 'array'],
            'approved_services.*' => ['string', 'max:120'],
            'risk_rating' => ['sometimes', 'integer', 'between:0,10'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
