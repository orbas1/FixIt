<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ServicemanResource extends BaseResource
{
    protected $showSensitiveAttributes = true;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'code' => $this->code,
            'status' => $this->status,
            'served' => $this->served,
            'experience_interval' => $this->experience_interval,
            'experience_duration' => $this->experience_duration,
            'total_days_experience' => $this->total_days_experience,
            'serviceman_review_ratings' => $this->serviceman_review_ratings,
            'deleted_at' => $this->deleted_at,
            'description' => $this->description,
            'primary_address' => $this?->primary_address ? [
                'id' => $this?->primary_address?->id,
                'area' => $this?->primary_address?->area,
                'postal_code' => $this?->primary_address?->postal_code,
                'address' => $this?->primary_address?->address,
                'street_address' => $this?->primary_address?->street_address,
                'is_primary' => $this?->primary_address?->is_primary,
                'type' => $this?->primary_address?->type,
                'alternative_name' => $this?->primary_address?->alternative_name,
                'alternative_phone' => $this?->primary_address?->alternative_phone,
                'country' => [
                    'id' => $this?->primary_address?->country->id,
                    'name' => $this?->primary_address?->country->name,
                ],
                'state' => [
                    'id' => $this?->primary_address?->state?->id,
                    'name' => $this?->primary_address?->state?->name
                ]
            ] : null,
            'media' => $this->media ? $this->media->map(function($media){
                return [
                    'original_url' => $media->original_url
                ];  
            }) : [],
            'known_languages' => $this->knownLanguages ? $this->knownLanguages->map(function($media){
                return [
                    'id' => $media->id,
                    'key' => $media->key
                ];  
            }) : [],
        ];
    }

    public function getMediaAttributes()
    {
        if ($this->media) {
            return $this->media->map(function ($media) {
                return collect($media)->except([
                    'model_type',
                    'model_id',
                    'uuid',
                    'file_name',
                    'mime_type',
                    'disk',
                    'conversions_disk',
                    'updated_at',
                    'preview_url'
                ]);
            });
        }
    }
}
