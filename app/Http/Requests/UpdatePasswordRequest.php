<?php

namespace App\Http\Requests;

use App\Rules\MatchOldPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class UpdatePasswordRequest extends FormRequest
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
        $rule = [
            'current_password' => ['required', new MatchOldPassword(Request::is('backend/account/password/update') ? auth()->user()->id : $this->route('id'))],
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|same:new_password',
        ];

        return $rule;
    }
}