<?php

namespace App\Http\Requests\API\Security\Mfa;

use Illuminate\Foundation\Http\FormRequest;

class VerifyMfaChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'challenge_id' => ['required', 'uuid', 'exists:mfa_challenges,id'],
            'code' => ['nullable', 'string', 'max:12'],
            'recovery_code' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('code') && !$this->filled('recovery_code')) {
                $validator->errors()->add('code', __('Provide a verification code or a recovery code.'));
            }

            if ($this->filled('code') && $this->filled('recovery_code')) {
                $validator->errors()->add('code', __('Choose either the authenticator code or a recovery code, not both.'));
            }
        });
    }
}
