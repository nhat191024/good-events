<?php

namespace App\Http\Resources\Api\Partner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Bavix\Wallet\Models\Transaction */
class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'new_balance' => (string) $this->meta['new_balance'] ?? null,
            'old_balance' => (string) $this->meta['old_balance'] ?? null,
            'reason' => $this->meta['reason'] ?? null,
            'created_at' => optional($this->created_at)->diffForHumans(),
            'created_at_timestamp' => optional($this->created_at)->toISO8601String(),
        ];
    }
}
