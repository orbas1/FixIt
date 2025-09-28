<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDashboardHighestRatedProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_favourite' => $this->is_favourite,
            'is_favourite_id' => $this->is_favourite_id,
            'location_cordinates' => $this?->location_cordinates,
            'experience_duration' => $this->experience_duration,
            'experience_interval' => $this->experience_interval,
            'review_ratings' => $this->review_ratings,
            'is_featured' => $this->is_featured,
            'is_verified' => $this->is_verified,
            'bookings_count' => $this->bookings_count,
            'media' => $this->getMediaList(),
        ];
    }

    private function getMediaList()
    {
        return $this->whenLoaded('media', function () {
            return $this->media->map(function ($media) {
                return collect($media)->only(['original_url']);
            })->values();
        }, []);
    }
}
