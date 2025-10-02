<?php

namespace App\Http\Requests\API\DataGovernance\Dpia;

use App\Models\DpiaRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDpiaRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', DpiaRecord::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'data_asset_id' => ['required', 'integer', 'exists:data_assets,id'],
            'status' => ['sometimes', 'string', Rule::in(['draft', 'in_review', 'mitigation_required'])],
            'mitigation_actions' => ['nullable', 'array'],
            'mitigation_actions.*.action' => ['required_with:mitigation_actions', 'string'],
            'mitigation_actions.*.owner' => ['nullable', 'string'],
            'residual_risks' => ['nullable', 'array'],
            'residual_risks.*.risk' => ['required_with:residual_risks', 'string'],
            'residual_risks.*.status' => ['nullable', 'string'],
        ];
    }
}
