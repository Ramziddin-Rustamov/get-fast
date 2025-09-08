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
            'booking_id' => $this->id,
            'seats_booked' => $this->seats_booked,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'trip' => [
                'id' => $this->trip->id,

                'start_region_id' => $this->trip->start_region_id,
                'end_region_id' => $this->trip->end_region_id,
                'start_district_id' => $this->trip->start_district_id,
                'end_district_id' => $this->trip->end_district_id,
                'start_quarter_id' => $this->trip->start_quarter_id,
                'end_quarter_id' => $this->trip->end_quarter_id,


                'start_time' => $this->trip->start_time,
                'end_time' => $this->trip->end_time,
                'price_per_seat' => $this->trip->price_per_seat,
                'available_seats' => $this->trip->available_seats,
                'status' => $this->trip->status,
                'from_latitude' => $this->trip->startPoint->lat,
                'from_longitude' => $this->trip->startPoint->long,
                'to_latitude' => $this->trip->endPoint->lat,
                'to_longitude' => $this->trip->endPoint->long
            ],
            'booked_users' => [
                'passengers' => $this->passengers->map(function ($passenger) {
                    return [
                        'id' => $passenger->id,
                        'name' => $passenger->name,
                        'phone' => $passenger->phone,
                    ];
                })
            ] ?? null,
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
