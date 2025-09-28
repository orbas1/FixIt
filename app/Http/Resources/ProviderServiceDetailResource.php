<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderServiceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $locale = $request->header('Accept-Lang') ?? app()->getLocale();
        return [
          'id' => $this?->id,
          'title' => $this?->getTranslation('title',$locale),
          'price' => $this?->price,
          'status' => $this?->status,
          'duration' => $this?->duration,
          'description' => $this?->description,
          'content' => $this?->content,
          'duration_unit' => $this?->duration_unit,
          'discount' => $this?->discount,
          'parent_id' => $this?->parent_id,
          'type' => $this?->type,
          'video' => $this?->video,
          'is_featured' => $this?->is_featured,
          'is_favourite' => $this?->is_favourite,
          'is_favourite_id' => $this?->is_favourite_id,
          'is_advertised' => $this?->is_advertised,
          'service_rate' => $this?->service_rate,
          'required_servicemen' => $this?->required_servicemen,
          'meta_description' => $this?->meta_description,
          'per_serviceman_commission' => $this?->per_serviceman_commission,
          'highest_commission' => $this?->highest_commission,
          'additional_services' => $this?->additionalServices ? $this?->additionalServices?->map(function($addOn){
                return [
                    'id' => $addOn?->id,
                    'title' => $addOn?->title,
                    'price' => $addOn?->price,
                    'media' => $addOn?->media ? $addOn?->media?->map(function($media){
                        return [
                            'original_id' => $media?->original__url
                        ];
                    }) : [], 
                ];
          }) : [],
          'media' => $this?->media
          ->filter(function ($media) use ($locale) {
              return isset($media->custom_properties['language']) &&
                  $media->custom_properties['language'] === $locale;
          })->map(function ($media) {
              return collect($media)->only(['original_url', 'collection_name', 'id']);
          }),
          'user' => $this?->whenLoaded('user', function () use ($request) {
              return [
                  'id' => $this?->user?->id,
                  'name' => $this?->user?->name,
                  'review_ratings' => $this?->user?->review_ratings,
                  'media' => $this?->user->media->map(function ($media) {
                          return collect($media)->only(['original_url']);
                      }),
                  'experience_interval' => $this?->user?->experience_interval,
                  'experience_duration' => $this?->user?->experience_duration,
                  'served' => $this?->user?->served,
                  'fcm_token' => $this?->user?->fcm_token
              ];
          }),
          'categories' => $this->categories->map(function($category){
                return [    
                    'id' => $category->id,
                    'title' => $category->title
                ];
           }),
          'faqs' => $this?->whenLoaded('faqs', function () {
              return $this?->faqs->map(function ($faq) {
                  return [
                      'question' => $faq->question,
                      'answer'   => $faq->answer,
                  ];
              });
          }),
          'taxes' => $this?->taxes ? $this?->taxes->map(function($tax){
                return [
                    'id' => $tax->id,
                    'name' => $tax->name,
                    'rate' => $tax->rate,
                ];
          }) : [],
          'related_services' => $this?->related_services->map(function ($related_service) use ($request) {

            $locale = $request->header('Accept-Lang') ?? app()->getLocale();
            return [
                    'id' => $related_service->id,
                    'title' => $related_service->getTranslation('title', $locale),
                    'is_favourite' => $related_service->is_favourite,
                    'is_favourite_id' => $related_service->is_favourite_id,
                    'categories' => $related_service->categories->take(1)->map(function($category){
                        return [    
                            'title' => $category->title
                        ];
                    }),
                    'service_rate' => $related_service->service_rate,
                    'required_servicemen' => $related_service->required_servicemen,
                    'price' => $related_service->price,
                    'media' => $related_service->getMedia('thumbnail')
                    ->filter(function ($media) use ($locale) {
                        return isset($media->custom_properties['language']) &&
                            $media->custom_properties['language'] === $locale;
                    })
                    ->map(function ($media) {
                        return collect($media)->only(['original_url']);
                    }),
                ];
             }),
             'reviews' => $this?->reviews ? $this?->reviews->map(function($review){
                return [
                    'rating' => $review?->rating,
                    'description' => $review?->description,
                    'created_at' => $review?->created_at,
                    'consumer' => [
                        'name' => $review?->consumer?->name,
                        'media' =>  $review?->consumer?->media ? $review?->consumer?->media->map(function($media){
                            return [
                                'original_url' => $media->original_url 
                            ];
                        }) : [],  
                    ],
                ];
             }) : [],
        ];
    }
}
