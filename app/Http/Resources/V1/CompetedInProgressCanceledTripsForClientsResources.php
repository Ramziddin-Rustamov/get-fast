<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CompetedInProgressCanceledTripsForClientsResources extends JsonResource
{
    public function toArray(Request $request): array
    {


        $origin = $this->startPoint
            ? $this->startPoint->lat . ',' . $this->startPoint->long
            : null;

        $destination = $this->endPoint
            ? $this->endPoint->lat . ',' . $this->endPoint->long
            : null;

        $googleMapUrl = ($origin && $destination)
            ? 'https://www.google.com/maps/dir/?api=1'
            . '&origin=' . urlencode($origin)
            . '&destination=' . urlencode($destination)
            : null;




        $start_time = $this->start_time ? Carbon::parse($this->start_time) : null;
        $end_time = $this->end_time ? Carbon::parse($this->end_time) : null;

        $lang = auth()->user()->authLanguage->language ?? 'uz';

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
            'start_region' => $this->startRegion->{'name_' . $lang} ?? $this->startRegion->name_uz ?? null,
            'end_region' => $this->endRegion->{'name_' . $lang} ?? $this->endRegion->name_uz ?? null,
            'start_district' => $this->startDistrict->{'name_' . $lang} ?? $this->startDistrict->name_uz ?? null,
            'end_district' => $this->endDistrict->{'name_' . $lang} ?? $this->endDistrict->name_uz ?? null,

            'start_quarter' => $this->startQuarter->name ?? null,
            'end_quarter' => $this->endQuarter->name ?? null,

            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'duration' => $duration_formatted, // Davomiylik (soatlar va daqiqalarda)
            'price_per_seat' => $this->price_per_seat,
            'total_seats' => $this->total_seats,
            'available_seats' => $this->available_seats,
            'google_map_url' => $googleMapUrl,
            'start_lat' => $this->startPoint->lat,
            'start_long' => $this->startPoint->long,
            'end_lat' => $this->endPoint->lat,
            'end_long' => $this->endPoint->long,
            'status' => $this->status,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->format('Y-m-d H:i:s') : null,
            'driver' => $this->driver ? [
                'id' => $this->driver->id,
                'first_name' => $this->driver->first_name ?? null,
                'last_name' => $this->driver->last_name ?? null,
                'phone' => $this->driver->phone ?? null,
                'role' => $this->driver->role ?? null,
            ] : 'No driver data',
            'bookings' => $this->bookings->where('user_id', auth()->user()->id)
                ->values()->map(function ($booking) {
                    return [
                        'booked_by_user' => [
                            'id' => $booking->user->id,
                            'first_name' => $booking->user->first_name,
                            'last_name' => $booking->user->last_name,
                            'phone' => $booking->user->phone,
                            'email' => $booking->user->email,
                            'booking_status' => $booking->status,
                        ],
                        'passengers' => $booking->passengers
                            ->where('booking_id', $booking->id)
                            ->values()
                            ->map(function ($passenger) use ($booking) {
                                return [
                                    'booking_status' => $booking->status,
                                    'name' => $passenger->name,
                                    'phone' => $passenger->phone,
                                    'longaitude' => $passenger->longitude,
                                    'latitude' => $passenger->latitude
                                ];
                            }),
                    ];
                }),

            'vehicle' => $this->vehicle ? [
                'id' => $this->vehicle->id,
                'model' => $this->vehicle->model ?? null,
                'seats' => $this->vehicle->seats ?? null,
                'car_number' => $this->vehicle->car_number ?? null,
                'color' => [
                    'id' => $this->vehicle->color->id,
                    'code' => $this->vehicle->color->code
                ] ?? null,
            ] : 'No vehicle data',

        ];
    }
}
