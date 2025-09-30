<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\EscrowTransaction */
class EscrowTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'direction' => $this->direction,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'occurred_at' => optional($this->occurred_at)->toIso8601String(),
        ];
    }
}
