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
            'trip' => [
                'id' => $this->trip_id,
                'start_location' =>$this->trip->startQuarter->district->region->name . ' - ' . $this->trip->startQuarter->district->name . ' - ' . $this->trip->startQuarter->name,
                'end_location' => $this->trip->endQuarter->district->region->name . ' - ' . $this->trip->endQuarter->district->name . ' - ' . $this->trip->endQuarter->name,
                'start_time' => $this->trip->start_time,
                'end_time' => $this->trip->end_time
            ] ?? null,


        ];
    }
}
