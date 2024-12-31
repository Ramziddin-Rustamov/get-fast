<?php
namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'driver' => [
                'id' => $this->driver_id,
                'name' => $this->driver->name ?? null, // Haydovchi ma'lumotlari
            ],
            'vehicle' => [
                'id' => $this->vehicle_id,
                'name' => $this->vehicle->name ?? null, // Transport vositasi ma'lumotlari
            ],
            'start_location' => $this->start_location,
            'end_location' => $this->end_location,
            'start_time' => $this->start_time->toDateTimeString(),
            'end_time' => $this->end_time ? $this->end_time->toDateTimeString() : null,
            'price_per_seat' => $this->price_per_seat,
            'total_seats' => $this->total_seats,
            'available_seats' => $this->available_seats,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
