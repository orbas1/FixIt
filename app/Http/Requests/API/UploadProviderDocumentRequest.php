<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UploadProviderDocumentRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'identity_no' => 'required',
            'document_id' => 'required|exists:documents,id',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'user_document_id' => 'nullable|exists:user_documents,id',
            'notes' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'identity_no.required' => 'Please enter the identity number.',
            'identity_no.string' => 'The identity number must be a string.',
            'identity_no.max' => 'The identity number may not be greater than 255 characters.',
            'document_id.required' => 'Please select a document type.',
            'document_id.exists' => 'The selected document type is invalid.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Each image must be a JPG, JPEG, or PNG file.',
            'images.*.max' => 'Each image must be less than 2MB.',
            'user_document_id.exists' => 'The selected document does not exist.',
            'notes.string' => 'Notes must be a string.',
            'notes.max' => 'Notes may not be greater than 255 characters.',
        ];
    }
}
