<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderDocumentRequest extends FormRequest
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
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Please select Provider.',
            'document_id.required' => 'Please select Document.',
            'identity_no.required' => 'Document Number Is Required.',
        ];
    }
}
