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

        return [
            'id' => $this->id,
            'seats_booked' => $this->seats_booked,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'trip' => [
                'id' => $this->trip->id,
                'from_where' => $this->trip->startQuarter->name . ', ' . $this->trip->startQuarter->district->name . ', ' . $this->trip->startQuarter->district->region->name,
                'to_where' => $this->trip->endQuarter->name . ', ' . $this->trip->endQuarter->district->name . ', ' . $this->trip->endQuarter->district->region->name,
                'start_time' => $this->trip->start_time,
                'end_time' => $this->trip->end_time,

            ],
            'client' => [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'email' => $this->user->email,
                'role' => $this->user->role,
                'phone' => $this->user->phone
            ],
            'driver' => $this->trip->driver ? [
                'id' => $this->trip->driver->id,
                'first_name' => $this->trip->driver->first_name,
                'last_name' => $this->trip->driver->last_name,
                'email' => $this->trip->driver->email,
                'role' => $this->trip->driver->role,
                'phone' => $this->trip->driver->phone
            ] : null, // Driver mavjud bo'lmasa null

            'vehicle' => $this->trip->vehicle ? [
                'id' => $this->trip->vehicle->id,
                'model' => $this->trip->vehicle->model,
                'car_number' => $this->trip->vehicle->car_number,
                'total_seats' => $this->trip->vehicle->seats,
                'color' => [
                    'title_uz' => $this->trip->vehicle->color->title_uz,
                    'title_ru' => $this->trip->vehicle->color->title_ru,
                    'title_en' => $this->trip->vehicle->color->title_en,
                    'color_code' => $this->trip->vehicle->color->code


                ]
            ] : null,

        ];
    }
}
