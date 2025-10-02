<?php

namespace App\Http\Requests\API\DataGovernance\Dpia;

use Illuminate\Foundation\Http\FormRequest;

class EnsureDpiaCoverageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('data_asset')) ?? false;
    }

    public function rules(): array
    {
        return [
            'mitigation_actions' => ['sometimes', 'array'],
            'mitigation_actions.*.action' => ['required_with:mitigation_actions', 'string'],
            'mitigation_actions.*.owner' => ['nullable', 'string'],
            'residual_risks' => ['sometimes', 'array'],
            'residual_risks.*.risk' => ['required_with:residual_risks', 'string'],
            'residual_risks.*.status' => ['nullable', 'string'],
        ];
    }
}
