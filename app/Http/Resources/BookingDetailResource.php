<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $locale = request()->header('Accept-Lang') ?? app()->getLocale();
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'date_time' => $this->date_time,
            'service_price' => $this->service_price,
            'type' => $this->type,
            'per_serviceman_charge' => $this->per_serviceman_charge,
            'platform_fees' => $this->platform_fees,
            'platform_fees_type' => $this->platform_fees_type,
            'required_servicemen' => $this->required_servicemen,
            'total_extra_servicemen' => $this->total_extra_servicemen,
            'total_servicemen' => $this->total_servicemen,
            'total_extra_servicemen_charge' => $this->total_extra_servicemen_charge,
            'coupon_total_discount' => $this->coupon_total_discount,
            'subtotal' => $this->subtotal,
            'total_tax' => $this->tax,
            'total' => $this->total,
            'grand_total_with_extras' => $this->grand_total_with_extras,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'invoice_url' => $this->invoice_url,
            'taxes' => $this?->taxes ? $this->taxes->map(function($tax){
                return [
                    'id' => $tax?->id,
                    'name' => $tax?->name,
                    'rate' => (float) $tax?->pivot?->rate,
                    'amount' => (float) $tax?->pivot?->amount
                ];
            }) : null,
            'consumer' => [
                'id' => $this?->consumer?->id,
                'name' => $this?->consumer?->name,
                'email' => $this?->consumer?->email,
                'phone' => $this?->consumer?->phone,
                'code' => $this?->consumer?->code,
                'fcm_token' => $this?->consumer?->fcm_token,
                'primary_address' => $this?->consumer?->primary_address ? [
                    'area' => $this?->consumer?->primary_address?->area,
                    'address' => $this?->consumer?->primary_address?->address,
                    'postal_code' => $this?->consumer?->primary_address?->postal_code,
                    'country' => [
                        'id' => $this?->consumer?->primary_address?->country->id,
                        'name' => $this?->consumer?->primary_address?->country->name,
                    ],
                    'state' => [
                        'id' => $this?->consumer?->primary_address?->state?->id,
                        'name' => $this?->consumer?->primary_address?->state?->name
                    ]
                ] : null,
                'media' => $this?->consumer?->media->map(function ($media) {
                    return collect($media)->only(['original_url']);
                }),
            ],
            'provider' => [
                'id' => $this->provider?->id,
                'name' => $this->provider?->name,
                'role' => $this->provider?->role?->name,
                'review_ratings' => $this->provider?->review_ratings,
                'experience_interval' => $this->provider?->experience_interval,
                'experience_duration' => $this->provider?->experience_duration,
                'media' => $this?->provider?->media ? $this?->provider?->media->map(function ($media) {
                        return collect($media)->only(['original_url']);
                    }) : null,
            ],
            'servicemen' => $this->servicemen->map(function ($serviceman) {
                return [
                    'id' => $serviceman?->id,
                    'name' => $serviceman?->name,
                    'code' => $serviceman?->code,
                    'phone' => $serviceman?->phone,
                    'role' => $serviceman?->role?->name,
                    'fcm_token' => $serviceman->fcm_token,
                    'review_ratings' => $serviceman->review_ratings,
                    'experience_interval' => $serviceman?->experience_interval,
                    'experience_duration' => $serviceman?->experience_duration,
                    'media' => $serviceman?->media ? $serviceman->media->map(function ($media) {
                        return collect($media)->only(['original_url']);
                    }) : null,
                ];
            }),
            'service' => $this->whenLoaded('service', function () use ($request) {
                $locale = $request->header('Accept-Lang') ?? app()->getLocale();
                return [
                    'id' => $this?->service?->id,
                    'type' => $this?->service?->type,
                    'title' => $this?->service?->getTranslation('title', $locale),
                    'price' => $this?->service?->price,
                    'service_rate' => $this?->service?->service_rate,
                    'discount' => $this?->service?->discount,
                    'discount_amount' => $this->service?->discount_amount,
                    'total_tax_amount' => $this->service?->total_tax_amount,
                    'destination_location' => $this?->service?->destination_location,
                    'media' => $this?->service?->getMedia('thumbnail')->take(1)->map(function ($media) {
                        return collect($media)->only(['original_url']);
                    }),
                    'reviews' => $this?->service?->reviews->map(function ($review) {
                        return [
                            'rating' => $review?->rating,
                            'description' => $review?->description,
                            'consumer' => $review?->consumer ? [
                                'id' => $review?->consumer?->id,
                                'name' => $review?->consumer?->name,
                                'media' => $review?->consumer?->media ? $review?->consumer?->media->map(function($media){
                                    return [
                                        'original_url' => $media->original_url
                                    ];
                                }) : [],
                            ] : []
                        ];
                    }),
                    'categories' => $this->service->categories->take(1)->map(function($category){
                        return [
                            'title' => $category->title
                        ];
                    }),
                ];
            }),
            'booking_status' => [
                'id' => $this?->booking_status?->id,
                'name' => $this?->booking_status?->name,
                'slug' => $this?->booking_status?->slug,
            ],
            'booking_status_logs' => $this->booking_status_logs->map(function ($bookingStatusLog) {
                return [
                    'title' => $bookingStatusLog->title,
                    'description' => $bookingStatusLog->description,
                    'created_at' => $bookingStatusLog->created_at,
                ];
            }),
            'address' => $this?->address,
            'booking_reasons' => $this?->bookingReasons->map(function ($bookingReason) {
                return [
                    'reason' => $bookingReason->reason,
                ];
            }),
            'service_proofs' => $this?->serviceProofs ? $this?->serviceProofs->map(function($serviceProof){
                return [
                    'id' => $serviceProof->id,
                    'title' => $serviceProof->title,
                    'description' => $serviceProof->description,
                    'media' => $serviceProof->media ? $serviceProof->media->map(function($media){
                        return [
                            'original_url' => $media->original_url,
                        ];
                    }) : [],
                ];
            }) : [],
            'extra_charges_total' => [
                'total_amount' => $this?->extra_charges->sum('total'),
                'tax_amount' => $this?->extra_charges->sum('tax_amount'),
                'grand_total' => $this->extra_charges->sum('grand_total'),
            ], 
            'extra_charges' => $this?->extra_charges ? $this?->extra_charges->map(function($extraCharge){
                return [
                    'id' => $extraCharge->id,
                    'title' => $extraCharge->title,
                    'per_service_amount' => $extraCharge->per_service_amount,
                    'no_service_done' => $extraCharge->no_service_done,
                    'payment_method' => $extraCharge->payment_method,
                    'payment_status' => $extraCharge->payment_status,
                    'total' => $extraCharge->total,
                    'tax_amount' => $extraCharge->tax_amount,
                    'grand_total' => $extraCharge->grand_total,
                ];
            }) : [],
            'additional_services' => $this?->additional_services ? $this->additional_services->map(function($add_on) use ($locale){
                return [
                    'id' => $add_on?->id,
                    'title' => $add_on?->getTranslation('title', $locale),
                    'price' => $add_on?->pivot?->price,
                    'qty' => $add_on?->pivot?->qty,
                    'total_price' => $add_on?->pivot?->total_price,
                ];
            }) : null,
            'zoom' => $this->whenLoaded('videoConsultation', function () {
                return [
                    'start_url'     => $this->videoConsultation?->start_url,
                    'join_url'      => $this->videoConsultation?->join_url,
                ];
            }),
        ];
    }
}