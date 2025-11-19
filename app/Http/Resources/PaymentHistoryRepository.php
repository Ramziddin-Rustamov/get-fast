<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentHistoryRepository extends JsonResource
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
            'amount' => $this->amount,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'pay_id' => $this->pay_id,
            'card' => [
                'id' => $this->card->id,
                'number' => $this->card->number,
                'expiry' => $this->card->expiry,
                'status' => $this->card->status
            ],
        ];
    }
}
