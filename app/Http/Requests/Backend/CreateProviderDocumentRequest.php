<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreateProviderDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'document_id' => 'required|exists:documents,id',
            'identity_no' => 'required|string',
            'status' => 'required',
            'image' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => __('validation.user_id_required'),
            'document_id.required' => __('validation.document_id_required'),
            'identity_no.required' => __('validation.identity_no_required'),
        ];
    }
}
