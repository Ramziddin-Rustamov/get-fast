<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class VehicleResource extends JsonResource
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
            'model' => $this->model,
            'car_number' => $this->car_number,
            'tech_passport_number' => $this->tech_passport_number,
            'seats' => $this->seats,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->format('Y-m-d H:i:s') : null,
            'vehicle_images' => collect(json_decode($this->images[0]->image_path ?? '[]'))
                ->map(fn($path) => asset('storage/' . $path))
                ->toArray(),
            'tech_passport_image' => $this->techPassport->image_path ? asset('storage/' . $this->techPassport->image_path) : null,
            'color' => [
                'title_uz' => $this->color->title_uz,
                'title_ru' => $this->color->title_ru,
                'title_en' => $this->color->title_en,
                'code' => $this->color->code
            ] ?? null,
            'you' => [
                'id' => $this->user_id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'phone' => $this->user->phone,
            ],
        ];
    }
}
