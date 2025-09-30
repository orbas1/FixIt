<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Escrow */
class EscrowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_request_id' => $this->service_request_id,
            'consumer_id' => $this->consumer_id,
            'provider_id' => $this->provider_id,
            'status' => $this->status,
            'amount' => (float) $this->amount,
            'amount_released' => (float) ($this->amount_released ?? 0.0),
            'amount_refunded' => (float) ($this->amount_refunded ?? 0.0),
            'available_amount' => $this->available_amount,
            'currency' => $this->currency,
            'hold_reference' => $this->hold_reference,
            'funded_at' => optional($this->funded_at)->toIso8601String(),
            'released_at' => optional($this->released_at)->toIso8601String(),
            'refunded_at' => optional($this->refunded_at)->toIso8601String(),
            'cancelled_at' => optional($this->cancelled_at)->toIso8601String(),
            'expires_at' => optional($this->expires_at)->toIso8601String(),
            'metadata' => $this->metadata,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'transactions' => EscrowTransactionResource::collection($this->whenLoaded('transactions')),
        ];
    }
}
