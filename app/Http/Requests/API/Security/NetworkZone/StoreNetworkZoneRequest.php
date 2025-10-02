<?php

namespace App\Http\Requests\API\Security\NetworkZone;

use App\Rules\ValidCidr;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNetworkZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('backend.security.zero_trust.manage')
            || $this->user()?->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'alpha_dash', 'max:190', 'unique:network_zones,slug'],
            'name' => ['required', 'string', 'max:190'],
            'type' => ['required', Rule::in(['trusted', 'restricted', 'blocked'])],
            'risk_level' => ['required', 'integer', 'between:0,100'],
            'ip_ranges' => ['required', 'array', 'min:1'],
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
