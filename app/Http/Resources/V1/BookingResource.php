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

        $lang = auth()->user()->authLanguage->language ?? 'uz';

        $origin = $this->trip->startPoint
            ? $this->trip->startPoint->lat . ',' . $this->trip->startPoint->long
            : null;

        $destination = $this->trip->endPoint
            ? $this->trip->endPoint->lat . ',' . $this->trip->endPoint->long
            : null;

        $googleMapUrl = ($origin && $destination)
            ? 'https://www.google.com/maps/dir/?api=1'
            . '&origin=' . urlencode($origin)
            . '&destination=' . urlencode($destination)
            : null;




        return [
            'booking_id' => $this->id,
            'seats_booked' => $this->seats_booked,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'trip' => [
                'id' => $this->trip->id,
                'start_region' => $this->trip->startRegion->{'name_' . $lang} ?? null,
                'end_region' => $this->trip->endRegion->{'name_' . $lang} ?? null,
                'start_district' => $this->trip->startDistrict->{'name_' . $lang} ?? null,
                'end_district' => $this->trip->endDistrict->{'name_' . $lang} ?? null,
                'start_quarter' => $this->trip->startQuarter->name ?? null,
                'end_quarter' => $this->trip->endQuarter->name ?? null,

                'start_time' => $this->trip->start_time,
                'end_time' => $this->trip->end_time,
                'price_per_seat' => $this->trip->price_per_seat,
                'available_seats' => $this->trip->available_seats,
                'status' => $this->trip->status,
                'from_latitude' => $this->trip->startPoint->lat,
                'from_longitude' => $this->trip->startPoint->long,
                'to_latitude' => $this->trip->endPoint->lat,
                'to_longitude' => $this->trip->endPoint->long,
                'google_map_url' => $googleMapUrl,
            ],

            'booked_by' => [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'email' => $this->user->email,
                'role' => $this->user->role,
                'phone' => $this->user->phone
            ],

            'passengers' => $this->passengers && $this->passengers->isNotEmpty()
                ? $this->passengers->map(function ($passenger) {
                    return [
                        'id' => $passenger->id ?? null,
                        'name' => $passenger->name  ?? null,
                        'phone' => $passenger->phone ?? null,
                        'longitude' => $passenger->longitude    ?? null,
                        'latitude' => $passenger->latitude    ?? null,
                        'status' => $passenger->status ?? null,
                        'passenger_status' => $passenger->status ?? null,
                        'booking_status' => $this->status ?? null,
                    ];
                })
                : null,
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
                    'id' => $this->trip->vehicle->color->id,
                    'color_code' => $this->trip->vehicle->color->code
                ]
            ] : null,

        ];
    }
}
