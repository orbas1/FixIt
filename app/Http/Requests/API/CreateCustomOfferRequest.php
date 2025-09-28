<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCustomOfferRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_servicemen_required' => 'required|boolean',
            'required_servicemen' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'provider_id' => 'nullable|exists:users,id',
            'status' => 'required|in:pending,accepted,rejected,expired',
            'started_at' => 'nullable|date',
            'ended_at' => 'nullable|date',
            'is_expired' => 'nullable|boolean',
            'category_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'category_ids.*' => [
                'integer',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
            ],
        ];
    }
}