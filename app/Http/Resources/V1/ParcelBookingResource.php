<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ParcelBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = auth()->user()->authLanguage->language ?? 'uz';
        $trip = $this->trip;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'total_price' => $this->total_price,
            'receiver_phone' => $this->receiver_phone,
            'pickup_lat' => $this->pickup_lat,
            'pickup_long' => $this->pickup_long,
            'dropoff_lat' => $this->dropoff_lat,
            'dropoff_long' => $this->dropoff_long,
            'parcel_description' => $this->parcel_description,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d H:i:s') : null,

            'type' => $this->type ? [
                'id' => $this->type->id,
                'name' => $this->type->{'name_' . $lang} ?? $this->type->name_uz,
                'icon' => $this->type->icon,
            ] : null,

            'trip' => $trip ? [
                'id' => $trip->id,
                'start_region' => $trip->startRegion->{'name_' . $lang} ?? null,
                'end_region' => $trip->endRegion->{'name_' . $lang} ?? null,
                'start_district' => $trip->startDistrict->{'name_' . $lang} ?? null,
                'end_district' => $trip->endDistrict->{'name_' . $lang} ?? null,
                'start_quarter' => $trip->startQuarter->name ?? null,
                'end_quarter' => $trip->endQuarter->name ?? null,
                'start_time' => $trip->start_time,
                'end_time' => $trip->end_time,
                'status' => $trip->status,
            ] : null,

            'sender' => $this->user ? [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'phone' => $this->user->phone,
            ] : null,

            'driver' => ($trip && $trip->driver) ? [
                'id' => $trip->driver->id,
                'first_name' => $trip->driver->first_name,
                'last_name' => $trip->driver->last_name,
                'phone' => $trip->driver->phone,
            ] : null,
        ];
    }
}
