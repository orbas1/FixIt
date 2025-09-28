<?php

namespace App\Http\Resources;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentRole = Helpers::getCurrentRoleName();

        return [
            'id' => $this?->id,
            'title' => $this?->title,
            'status' => $this?->status,
            'initial_price' => $this?->initial_price,
            'final_price' => $this?->final_price,
            'booking_date' => $this?->booking_date,
            'description' => $this?->description,
            'duration' => $this?->duration,
            'duration_unit' => $this?->duration_unit,
            'required_servicemen' => $this?->required_servicemen,
            'created_at' => $this?->created_at,
            'provider_id' => $this?->provider_id,
            'categories' => $this?->categories_data?->map(function ($cat) {
                return [
                    'id' => $cat?->id,
                    'title' => $cat?->title,
                ];
            }),
            'bids' => $currentRole === RoleEnum::PROVIDER
                ? optional(
                    $this->bids?->where('provider_id', Helpers::getCurrentUserId())->first()
                )?->only(['id', 'amount', 'status', 'provider_id', 'service_request_id'])
                : (
                    $this->bids?->count()
                    ? $this->bids->map(function ($bid) {
                        return [
                            'id' => $bid->id,
                            'amount' => $bid->amount,
                            'status' => $bid->status,
                            'provider_id' => $bid->provider_id,
                            'service_request_id' => $bid->service_request_id,
                            'provider' => [
                                'id' => $bid->provider?->id,
                                'name' => $bid->provider?->name,
                                'media' => $bid->provider?->media?->map(function ($media) {
                                    return [
                                        'original_url' => $media->original_url,
                                    ];
                                }) ?? null,
                                'review_rating' => $bid->provider?->review_ratings,
                            ],
                        ];
                    }) : []
                ),
            'user' => $this->whenLoaded('user', function () use ($request) {
                return [
                    'id' => $this?->user?->id,
                    'name' => $this->user?->name,
                    'email' => $this->user?->email,
                    'phone' => $this->user?->phone,
                    'code' => $this->user?->code,
                    'media' => $this->user->media->map(function ($media) {
                        return collect($media)->only(['original_url']);
                    }),
                ];
            }),
            'service' => $this->whenLoaded('service', function () use ($request) {
                return [
                    'id' => $this?->service?->id,
                    'title' => $this?->service?->title,
                    'service_rate' => $this?->service?->service_rate,
                    'price' => $this?->service?->price,
                    'type' => $this?->service?->type,
                    'required_servicemen' => $this?->service?->required_servicemen,
                    'media' => $this?->service?->media->map(function ($media) {
                        return collect($media)->only(['original_url']);
                    }),
                    'user' => $this->whenLoaded('user', function () use ($request) {
                        return [
                            'id' => $this?->user?->id,
                            'name' => $this?->user?->name,
                            'review_rating' => $this?->user?->review_ratings,
                            'media' => $this?->user?->media->map(function ($media) {
                                return collect($media)->only(['original_url']);
                            }),
                        ];
                    }),
                ];
            }),
            'media' => $this?->media ? $this?->media->map(function ($media) {
                return [
                    'original_url' => $media?->original_url,
                ];
            }) : [],
        ];
    }
}
