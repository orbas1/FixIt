<?php

namespace App\Http\Resources;

use App\Enums\RoleEnum;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSelfResource extends JsonResource
{
    public function toArray($request)
    {
        $currentRoleName = $this?->role?->name;
        if(RoleEnum::CONSUMER === $currentRoleName){
            return [
                'id' => $this?->id,
                'name' => $this?->name,
                'email' => $this?->email,
                'phone' => $this?->phone,
                'code' => $this?->code,
                'is_verified' => $this?->is_verified,
                'type' => $this?->type,
                'location_cordinates' => $this?->location_cordinates,
                'fcm_token' => $this->fcm_token,
                'media' => $this?->media?->map(function ($media) {
                    return [
                        'original_url' => $media?->original_url,
                    ];
                }),
                'role' => $this?->role?->name,
                'wallet' => [
                    'id' => $this?->wallet?->id,
                    'balance' => $this?->wallet?->balance
                ],
            ];
        } elseif (RoleEnum::PROVIDER === $currentRoleName) {
            return [
                'id' => $this?->id,
                'name' => $this?->name,
                'email' => $this?->email,
                'served' => $this?->served,
                'phone' => $this?->phone,
                'code' => $this?->code,
                'is_verified' => $this?->is_verified,
                'type' => $this?->type,
                'experience_interval' => $this?->experience_interval,
                'experience_duration' => $this?->experience_duration,
                'location_cordinates' => $this?->location_cordinates,
                'fcm_token' => $this->fcm_token,                
                'description' => $this->description,
                'provider_id' => $this->provider_id,                
                // 'active_subscription' => $this?->active_subscription ?? null,
                'subscription_reminder_note' => $this?->subscription_reminder_note ?? null,
                'media' => $this?->media?->map(function ($media) {
                    return [
                        'original_url' => $media?->original_url,
                    ];
                }),
                'role' => $this?->role?->name,
                'company' => [
                    'id' => $this?->company?->id,
                    'email' => $this?->company?->email,
                    'name' => $this?->company?->name,
                    'phone' => $this?->company?->phone,
                    'code' => $this?->company?->code,
                    'media' => $this?->company?->media->map(function ($media) {
                        return [
                            'original_url' => $media?->original_url,
                        ];
                    }),
                    'primary_address' => $this?->company?->primary_address ? [
                        'area' => $this?->company?->primary_address?->area,
                        'area' => $this?->company?->primary_address?->area,
                        'city' => $this?->company?->primary_address?->city,
                        'postal_code' => $this?->company?->primary_address?->postal_code,
                        'country' => [
                            'id' => $this?->company?->primary_address?->country->id,
                            'name' => $this?->company?->primary_address?->country->name,
                        ],
                        'state' => [
                            'id' => $this?->company?->primary_address?->state?->id,
                            'name' => $this?->company?->primary_address?->state?->name
                        ]
                    ] : null,
                ],
                'provider_wallet' => [
                    'id' => $this?->providerWallet?->id,
                    'balance' => $this?->ProviderWallet?->balance
                ],
                'serviceman_wallet' => [
                    'id' => $this?->servicemanWallet?->id,
                    'balance' => $this?->servicemanWallet?->balance
                ],
                'zones' => $this?->zones ? $this?->zones->map(function($zone){
                    return [
                        'id' => $zone->id,
                        'name' => $zone->name
                    ];
                }) : [],
                // 'provider' => [
                //     'id' => $this?->provider?->id,
                //     'name' => $this?->provider?->name,
                //     'review_ratings' => $this?->provider?->review_ratings,
                //     'media' => $this?->provider?->media->map(function ($media) {
                //         return [
                //             'original_url' => $media?->original_url,
                //         ];
                //     }),
                // ],
                // 'known_languages' => $this?->knownLanguages,
                // 'active_subscription' => $this?->active_subscription,
                // 'zones' => $this?->zones?->map(function ($zone) {
                //     return [
                //         'id' => $zone?->id,
                //         'name' => $zone?->name,
                //         'status' => $zone?->status,
                //     ];
                // }),
            ];
        } elseif(RoleEnum::SERVICEMAN === $currentRoleName){
            return [
                'id' => $this?->id,
                'name' => $this?->name,
                'email' => $this?->email,
                'served' => $this?->served,
                'phone' => $this?->phone,
                'code' => $this?->code,
                'is_verified' => $this?->is_verified,
                'type' => $this?->type,
                'experience_interval' => $this?->experience_interval,
                'experience_duration' => $this?->experience_duration,
                'location_cordinates' => $this?->location_cordinates,
                'fcm_token' => $this->fcm_token,                
                'description' => $this->description,
                'provider_id' => $this->provider_id,                
                // 'active_subscription' => $this?->active_subscription ?? null,
                'subscription_reminder_note' => $this?->subscription_reminder_note ?? null,
                'primary_address' => $this?->primary_address ? [
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
                'media' => $this?->media?->map(function ($media) {
                    return [
                        'original_url' => $media?->original_url,
                    ];
                }),
                'role' => $this?->role?->name,
                'company' => [
                    'id' => $this?->provider?->company?->id,
                    'email' => $this?->provider?->company?->email,
                    'name' => $this?->provider?->company?->name,
                    'phone' => $this?->provider?->company?->phone,
                    'code' => $this?->provider?->company?->code,
                    'media' => $this?->provider?->company?->media->map(function ($media) {
                        return [
                            'original_url' => $media?->original_url,
                        ];
                    }),
                    'primary_address' => $this?->provider?->company?->primary_address ? [
                        'area' => $this?->provider?->company?->primary_address?->area,
                    ] : null,
                ],
                'provider_wallet' => [
                    'id' => $this?->providerWallet?->id,
                    'balance' => $this?->ProviderWallet?->balance
                ],
                'serviceman_wallet' => [
                    'id' => $this?->servicemanWallet?->id,
                    'balance' => $this?->servicemanWallet?->balance
                ],
                'zones' => $this?->zones ? $this?->zones->map(function($zone){
                    return [
                        'id' => $zone->id,
                        'name' => $zone->name
                    ];
                }) : [],
                // 'provider' => [
                //     'id' => $this?->provider?->id,
                //     'name' => $this?->provider?->name,
                //     'review_ratings' => $this?->provider?->review_ratings,
                //     'media' => $this?->provider?->media->map(function ($media) {
                //         return [
                //             'original_url' => $media?->original_url,
                //         ];
                //     }),
                // ],
                // 'known_languages' => $this?->knownLanguages,
                // 'active_subscription' => $this?->active_subscription,
                // 'zones' => $this?->zones?->map(function ($zone) {
                //     return [
                //         'id' => $zone?->id,
                //         'name' => $zone?->name,
                //         'status' => $zone?->status,
                //     ];
                // }),
            ];
        } 
    }
}
