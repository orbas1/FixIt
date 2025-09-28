<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this?->content,
            'media' => $this?->media ? $this?->media?->map(function($media){
                return [
                    'id' => $media->id,
                    'original_url' => $media->original_url
                ];
            }) : null
        ];
    }
}
