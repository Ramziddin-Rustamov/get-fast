<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $start_time = $this->start_time ? Carbon::parse($this->start_time) : null;
        $end_time = $this->end_time ? Carbon::parse($this->end_time) : null;

        // Davomiylikni hisoblash
        $duration = $start_time && $end_time ? $start_time->diff($end_time) : null;

        // Davomiylikni formatlash (kunlar, soatlar, daqiqalar)
        $duration_formatted = $duration
            ? sprintf(
                '%d kun, %d soat, %d daqiqa',
                $duration->d, // Kunlar
                $duration->h, // Soatlar
                $duration->i  // Daqiqalar
            )
            : null;
        return [
            'id' => $this->id,
            'seats_booked' => $this->seats_booked,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'trip' => [
                'id' => $this->trip->id,
                'start_location' => $this->trip->start_location,
                'end_location' => $this->trip->end_location,
                'start_time' => $this->trip->start_time,
                'end_time' => $this->trip->end_time,
                'duration' => $duration_formatted, // Davomiylik (soatlar va daqiqalarda)
                
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'role' => $this->user->role,
                'phone' => $this->user->phone
            ],
            'driver' => $this->trip->driver ? [
                'id' => $this->trip->driver->id,
                'name' => $this->trip->driver->name,
                'role' => $this->trip->driver->role,
                'phone' => $this->trip->driver->phone
            ] : null, // Driver mavjud bo'lmasa null
        
        ];
    }
}
