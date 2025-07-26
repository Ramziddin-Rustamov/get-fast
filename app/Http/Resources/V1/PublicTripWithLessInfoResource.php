<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PublicTripWithLessInfoResource extends JsonResource
{
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
            'from_where' => $this->startQuarter->name . ', ' . $this->startQuarter->district->name . ', ' . $this->startQuarter->district->region->name,
            'to_where' => $this->endQuarter->name . ', ' . $this->endQuarter->district->name . ', ' . $this->endQuarter->district->region->name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'duration' => $duration_formatted, // Davomiylik (soatlar va daqiqalarda)
            'price_per_seat' => $this->price_per_seat,
            'total_seats' => $this->total_seats,
            'available_seats' => $this->available_seats,
            'start_lat' => $this->startPoint->lat,
            'start_long' => $this->startPoint->long,
            'end_lat' => $this->endPoint->lat,
            'end_long' => $this->endPoint->long,
            'status' => $this->status,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d H:i:s') : null,
           'driver' => $this->driver ? [
                'id' => $this->driver->id,
                'name' => $this->driver->name ?? null,
                'role' => $this->driver->role ?? null,
            ] : 'No driver data',

            'vehicle' => $this->vehicle ? [
                'id' => $this->vehicle->id,
                'model' => $this->vehicle->model ?? null,
                'seats' => $this->vehicle->seats ?? null,
                'car_number' => $this->vehicle->car_number ?? null,
                'color' => [
                    'id' => $this->vehicle->color->id,
                    'title_uz' => $this->vehicle->color->title_uz,
                    'title_ru' => $this->vehicle->color->title_ru,
                    'title_en' => $this->vehicle->color->title_en,
                    'code' => $this->vehicle->color->code
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
