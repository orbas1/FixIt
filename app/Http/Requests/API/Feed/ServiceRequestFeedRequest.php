<?php

namespace App\Http\Requests\API\Feed;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequestFeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:50'],
            'status' => ['sometimes', 'in:open,pending,closed'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer'],
            'zone_ids' => ['sometimes', 'array'],
            'zone_ids.*' => ['integer'],
            'search' => ['sometimes', 'string', 'max:255'],
            'ordering' => ['sometimes', 'in:recent,expiring,budget_high,budget_low,distance'],
            'budget_min' => ['sometimes', 'numeric', 'min:0'],
            'budget_max' => ['sometimes', 'numeric', 'gte:budget_min'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'radius' => ['sometimes', 'numeric', 'min:1', 'max:200'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('category_ids') && is_string($this->category_ids)) {
            $this->merge([
                'category_ids' => array_values(array_filter(explode(',', $this->category_ids))),
            ]);
        }

        if ($this->has('zone_ids') && is_string($this->zone_ids)) {
            $this->merge([
                'zone_ids' => array_values(array_filter(explode(',', $this->zone_ids))),
            ]);
        }
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 15);
    }

    public function status(): string
    {
        return $this->input('status', 'open');
    }

    public function categories(): array
    {
        return array_map('intval', $this->input('category_ids', []));
    }

    public function zoneIds(): array
    {
        return array_map('intval', $this->input('zone_ids', []));
    }

    public function ordering(): string
    {
        return $this->input('ordering', 'recent');
    }

    public function budgetMin(): ?float
    {
        return $this->filled('budget_min') ? (float) $this->input('budget_min') : null;
    }

    public function budgetMax(): ?float
    {
        return $this->filled('budget_max') ? (float) $this->input('budget_max') : null;
    }

    public function latitude(): ?float
    {
        return $this->filled('latitude') ? (float) $this->input('latitude') : null;
    }

    public function longitude(): ?float
    {
        return $this->filled('longitude') ? (float) $this->input('longitude') : null;
    }

    public function hasLocation(): bool
    {
        return $this->latitude() !== null && $this->longitude() !== null;
    }

    public function radius(): ?float
    {
        return $this->filled('radius') ? (float) $this->input('radius') : null;
    }
}
