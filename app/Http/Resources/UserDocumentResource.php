<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status,
            'identity_no' => $this->identity_no,
            'media' => $this->getMediaList(),
            'document' => $this?->document?->title
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
