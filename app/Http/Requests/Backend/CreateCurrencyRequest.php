<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreateCurrencyRequest extends FormRequest
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
            'code' => ['required', 'string', 'unique:currencies,code,NULL,id,deleted_at,NULL'],
            'image' => 'required',
            'symbol' => ['string'],
            'symbol_position' => ['required','string'],
            'no_of_decimal' => ['min:0'],
            'exchange_rate' => ['min:0'],
        ];
    }
}
