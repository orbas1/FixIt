<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomOfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'price'             => (float) $this->price,
            'status'            => $this->status,
            'is_expired'        => (bool) $this->is_expired,
            'required_servicemen'=> $this->required_servicemen,
            'duration'          => $this->duration,
            'created_at'        => $this->created_at,

            // 'provider' => [
            //     'id'   => $this->provider?->id,
            //     'name' => $this->provider?->name,
            //     'media' => $this->provider?->media ? $this->provider?->media->map(function($media){
            //             return [
            //                 'original_url' => $media->original_url,
            //             ];
            //         }) : [],
            // ],

            'user' => [
                'id'   => $this->user?->id,
                'name' => $this->user?->name,
                'media' => $this->user?->media ? $this->user?->media->map(function($media){
                        return [
                            'original_url' => $media->original_url,
                        ];
                    }) : [],
            ],
            
            'service' => $this?->service ? [
                    'id'    => $this->service->id,
                    'title' => $this->service->title,
                    'type'  => $this->service->type,
                    'price' => (float) $this->service->price,
                ] : null,
        ];
    }
}
