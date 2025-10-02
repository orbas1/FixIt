<?php

namespace App\Http\Requests\API\DataGovernance\DataResidencyZone;

use App\Models\DataResidencyZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDataResidencyZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', DataResidencyZone::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:data_residency_zones,code'],
            'name' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', Rule::in(array_keys(config('data_governance.residency_policies.regions')))],
            'country_codes' => ['required', 'array', 'min:1'],
            'country_codes.*' => ['string', 'size:2'],
            'default_controller' => ['nullable', 'string', 'max:255'],
            'approved_services' => ['nullable', 'array'],
            'approved_services.*' => ['string', 'max:120'],
            'risk_rating' => ['nullable', 'integer', 'between:0,10'],
            'is_active' => ['boolean'],
        ];
    }
}
