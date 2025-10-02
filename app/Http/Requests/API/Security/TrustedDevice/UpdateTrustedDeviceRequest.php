<?php

namespace App\Http\Requests\API\Security\TrustedDevice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTrustedDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $device = $this->route('trusted_device') ?? $this->route('trustedDevice');

        return $device !== null && $this->user()?->can('update', $device);
    }

    public function rules(): array
    {
        return [
            'display_name' => ['sometimes', 'string', 'max:190'],
            'trust_level' => ['sometimes', Rule::in(['trusted', 'pending', 'revoked', 'expired'])],
        ];
    }
}
