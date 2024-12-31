<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'booking' => [
                'id' => $this->booking_id,
                'seats_booked' => $this->booking->seats_booked ?? null,
                'total_price' => $this->booking->total_price ?? null,
            ],
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user->name ?? null,
            ],
            'amount' => $this->amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
