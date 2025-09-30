<?php

namespace App\Http\Resources\Feed;

use App\Services\Media\ImageVariantService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ServiceRequestFeedResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $coordinates = $this->location_coordinates;

        if ((!is_array($coordinates) || empty($coordinates)) && $this->latitude !== null && $this->longitude !== null) {
            $coordinates = [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ];
        }

        $latestBid = $this->whenLoaded('bids', function () {
            return $this->bids->first();
        });

        $imageVariantService = app(ImageVariantService::class);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'summary' => Str::limit(strip_tags((string) $this->description), 220),
            'status' => $this->status,
            'duration' => [
                'value' => $this->duration,
                'unit' => $this->duration_unit,
            ],
            'required_servicemen' => $this->required_servicemen,
            'budget' => [
                'initial' => $this->initial_price,
                'final' => $this->final_price,
                'currency' => config('app.currency', 'USD'),
            ],
            'bids' => [
                'total' => $this->bids_total ?? 0,
                'pending' => $this->bids_pending ?? 0,
                'accepted' => $this->bids_accepted ?? 0,
                'latest' => $this->when($latestBid !== null, function () use ($latestBid) {
                    return [
                        'id' => $latestBid->id,
                        'amount' => $latestBid->amount,
                        'status' => $latestBid->status,
                        'provider' => $latestBid->provider?->only(['id', 'name']),
                        'created_at' => optional($latestBid->created_at)->toIso8601String(),
                    ];
                }),
            ],
            'consumer' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'avatar' => $this->user?->media->first()?->original_url,
                ];
            }),
            'service' => $this->whenLoaded('service', function () {
                return [
                    'id' => $this->service?->id,
                    'title' => $this->service?->title,
                    'price' => $this->service?->price,
                ];
            }),
            'zones' => $this->whenLoaded('zones', function () {
                return $this->zones->map(function ($zone) {
                    return [
                        'id' => $zone->id,
                        'name' => $zone->name,
                    ];
                })->values();
            }),
            'attachments' => $this->transformAttachments($imageVariantService),
            'legacy_attachments' => $this->attachments ?? [],
            'location' => $this->when($coordinates !== null, function () use ($coordinates) {
                return [
                    'lat' => $coordinates['lat'] ?? null,
                    'lng' => $coordinates['lng'] ?? null,
                    'h3_index' => $this->h3_index,
                ];
            }),
            'distance_km' => $this->when(isset($this->distance_km), function () {
                return round((float) $this->distance_km, 2);
            }),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'booking_date' => optional($this->booking_date)->toIso8601String(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function transformAttachments(ImageVariantService $imageVariantService): array
    {
        if (! $this->relationLoaded('media')) {
            return [];
        }

        return $this->media
            ->where('collection_name', 'attachments')
            ->map(fn ($media) => $imageVariantService->transform($media))
            ->values()
            ->all();
    }
}
