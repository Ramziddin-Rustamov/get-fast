<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
                'role' => $this->user->role ?? null,
            ],
            'amount' => $this->amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
           'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->format('Y-m-d H:i:s') : null,
        ];
    }
}
