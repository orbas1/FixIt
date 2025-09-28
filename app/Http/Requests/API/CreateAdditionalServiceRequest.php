<?php

namespace App\Http\Requests\API;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class CreateAdditionalServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasRole('provider');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'parent_id' => 'required|exists:services,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $service = Service::where('id', $this->parent_id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$service) {
                $validator->errors()->add('parent_id', __('static.service.not_owned_by_you'));
            }
        });
    }
}
