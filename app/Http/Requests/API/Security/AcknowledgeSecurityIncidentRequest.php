<?php

namespace App\Http\Requests\API\Security;

use App\Models\SecurityIncident;
use Illuminate\Foundation\Http\FormRequest;

class AcknowledgeSecurityIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SecurityIncident|null $incident */
        $incident = $this->route('incident');

        return $incident && $this->user()?->can('acknowledge', $incident);
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
