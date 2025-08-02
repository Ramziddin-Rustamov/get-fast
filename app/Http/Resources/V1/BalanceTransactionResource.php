<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanceTransactionResource extends JsonResource
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
            'balance_before' => $this->balance_before,
            'amount' => $this->amount,
            'balance_after' => $this->balance_after,
            'reason' => $this->reason,
            'type' => $this->type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'trip' => $this->trip ? [
                'id' => $this->trip_id,
                'start_location' => optional(optional(optional($this->trip->startQuarter)->district)->region)->name . ' - ' .
                    optional(optional($this->trip->startQuarter)->district)->name . ' - ' .
                    optional($this->trip->startQuarter)->name,
                'end_location' => optional(optional(optional($this->trip->endQuarter)->district)->region)->name . ' - ' .
                    optional(optional($this->trip->endQuarter)->district)->name . ' - ' .
                    optional($this->trip->endQuarter)->name,
                'start_time' => $this->trip->start_time,
                'price_per_seat' => $this->trip->price_per_seat,
                'end_time' => $this->trip->end_time
            ] : null,
            'booking' => $this->booking ? [
                'id' => $this->id,
                'seats_booked' => $this->booking->seats_booked,
                'total_price' => $this->booking->total_price,
                'status' => $this->booking->status,
                'created_at' => $this->booking->created_at
            ] : null
        ];
    }
}
