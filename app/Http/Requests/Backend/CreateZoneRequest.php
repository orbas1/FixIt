<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use CodeZero\UniqueTranslation\UniqueTranslationRule;

class CreateZoneRequest extends FormRequest
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
            'name*' => ['required', 'string', 'max:255', UniqueTranslationRule::for('zones')->whereNull('deleted_at')],
            'place_points' => ['required'],
            'status' => ['min:0', 'max:1'],
        ];
    }
}
