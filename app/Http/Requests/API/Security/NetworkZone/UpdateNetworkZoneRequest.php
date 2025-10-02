<?php

namespace App\Http\Requests\API\Security\NetworkZone;

use App\Rules\ValidCidr;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNetworkZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('backend.security.zero_trust.manage')
            || $this->user()?->hasRole('admin');
    }

    public function rules(): array
    {
        $zoneId = $this->route('network_zone')?->id ?? $this->route('networkZone')?->id ?? null;

        return [
            'slug' => ['sometimes', 'alpha_dash', 'max:190', Rule::unique('network_zones', 'slug')->ignore($zoneId)],
            'name' => ['sometimes', 'string', 'max:190'],
            'type' => ['sometimes', Rule::in(['trusted', 'restricted', 'blocked'])],
            'risk_level' => ['sometimes', 'integer', 'between:0,100'],
            'ip_ranges' => ['sometimes', 'array', 'min:1'],
            'ip_ranges.*' => [new ValidCidr()],
            'device_tags' => ['nullable', 'array'],
            'device_tags.*' => ['string', 'max:120'],
            'enforced_controls' => ['nullable', 'array'],
            'enforced_controls.*' => ['string', 'max:120'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
