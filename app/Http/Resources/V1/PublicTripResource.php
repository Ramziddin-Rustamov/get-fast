<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PublicTripResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $lang = auth()->user()->authLanguage->language ?? 'uz';

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
            'start_region' => $this->startRegion->{'name_' . $lang} ?? null,
            'end_region' => $this->endRegion->{'name_' . $lang} ?? null,
            'start_district' => $this->startDistrict->{'name_' . $lang} ?? null,
            'end_district' => $this->endDistrict->{'name_' . $lang} ?? null,
            'start_quarter' => $this->startQuarter->{'name_' . $lang} ?? null,
            'end_quarter' => $this->endQuarter->{'name_' . $lang} ?? null,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'duration' => $duration_formatted, // Davomiylik (soatlar va daqiqalarda)
            'price_per_seat' => $this->price_per_seat,
            'total_seats' => $this->total_seats,
            'available_seats' => $this->available_seats,
            'start_lat' => $this->startPoint->lat ?? null,
            'start_long' => $this->startPoint->long ?? null,
            'end_lat' => $this->endPoint->lat ?? null,
            'end_long' => $this->endPoint->long ?? null,
            'status' => $this->status,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->format('Y-m-d H:i:s') : null,
            'driver' => $this->driver ? [
                'id' => $this->driver->id,
                'name' => $this->driver->first_name ?? null,
                'last_name' => $this->driver->last_name ?? null,
                'role' => $this->driver->role ?? null,
            ] : 'No driver data',

            'vehicle' => $this->vehicle ? [
                'id' => $this->vehicle->id,
                'model' => $this->vehicle->model ?? null,
                'seats' => $this->vehicle->seats ?? null,
                'car_number' => $this->vehicle->car_number ?? null,
                'color' => [
                    'id' => $this->vehicle->color->id,
                ] ?? null,
            ] : 'No vehicle data',

            'starting_point' => $this->startPoint ? [
                'id' => $this->startPoint->id,
                'lat' => $this->startPoint->lat,
                'long' => $this->startPoint->long,
            ] : 'No starting point data',

            'ending_point' => $this->endPoint ? [
                'id' => $this->endPoint->id,
                'lat' => $this->endPoint->lat,
                'long' => $this->endPoint->long,
            ] : 'No ending point data',

        ];
    }
}
