<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'admin_commission' => $this->admin_commission,
            'provider_commission' => $this->provider_commission,
            'provider_net_commission' => $this->provider_net_commission,
            'booking' => $this->booking ? [
                'id' => $this->booking?->id,
                'platform_fees' => $this->booking?->platform_fees,
                'booking_number' => $this->booking->booking_number,
                'total' => $this?->booking?->total,
                'created_at' => $this?->booking?->created_at,
                'service' => [
                    'id' => optional($this->booking->service)->id,
                    'title' => optional($this->booking->service)->title,
                ],
            ] : null,
            'serviceman_commissions' => $this?->serviceman_commissions ? $this?->serviceman_commissions->map(function ($sc) {
                return [
                    'serviceman_id' => $sc->serviceman_id,
                    'name' => $sc->serviceman->name ?? null,
                    'commission' => $sc->commission,
                ];
            }) : null,
            'created_at' => $this->created_at,
        ];
    }
}