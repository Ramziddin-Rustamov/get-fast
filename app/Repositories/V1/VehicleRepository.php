<?php

namespace App\Repositories\V1;

use App\Http\Requests\V1\StoreRequest;
use App\Http\Requests\V1\UpdateRequest;
use App\Http\Resources\V1\VehicleResource;
use App\Models\V1\Vehicle;
use App\Models\V1\VehicleImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Storage;

class VehicleRepository
{



    public function getAll()
    {
        return VehicleResource::collection(Vehicle::where('user_id', Auth::user()->id)->get());
    }

    public function findById($id)
    {
        $vehicle = Vehicle::where('user_id', Auth::user()->id)->find($id);

        if (is_null($vehicle)) {
            $messages = [
                'uz' => 'Avtomobil topilmadi.',
                'ru' => 'Автомобиль не найден.',
                'en' => 'Vehicle not found.',
            ];

            $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 404);
        }

        return response()->json(new VehicleResource($vehicle), 200);
    }
    public function create(StoreRequest $request)
    {
        try {
            $vehicle = DB::transaction(function () use ($request) {
                $data = $request->validated();

                $vehicle = new Vehicle();
                $vehicle->user_id = Auth::id();
                $vehicle->color_id = $data['car_color_id'];
                $vehicle->model = $data['car_model'];
                $vehicle->tech_passport_number = $data['tech_passport_number'];
                $vehicle->car_number = $data['vehicle_number'];
                $vehicle->seats = $data['seats'];
                $vehicle->save();


                return $vehicle;
            });

            $messages = [
                'uz' => 'Avtomobil muvaffaqiyatli yaratildi',
                'ru' => 'Автомобиль успешно создан',
                'en' => 'Vehicle created successfully',
            ];

            return response()->json([
                'status' => 'success',
                'message' => $messages[auth()->user()->authLanguage->language ?? 'uz'],
                'data' => new VehicleResource($vehicle)
            ], 201);
        } catch (\Exception $e) {


            $messages = [
                'uz' => 'Xatolik yuz berdi. Iltimos, keyinroq urinib ko‘ring.',
                'ru' => 'Произошла ошибка. Пожалуйста, попробуйте позже.',
                'en' => 'An error occurred. Please try again later.',
            ];

            $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

            return response()->json([
                'status' => 'error',
                'message' => $message,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function delete($id)
    {
        try {
            $vehicle = Vehicle::where('user_id', Auth::id())->find($id);

            if (is_null($vehicle)) {
                $messages = [
                    'uz' => 'Transport vositasi topilmadi.',
                    'ru' => 'Транспортное средство не найдено.',
                    'en' => 'Vehicle not found.',
                ];

                $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 404);
            }

            // Rasm fayllarini va yozuvlarni o‘chirish
            $images = VehicleImages::where('vehicle_id', $vehicle->id)->get();

            foreach ($images as $image) {
                if ($image->type === 'vehicle') {
                    $paths = json_decode($image->image_path, true);
                    foreach ($paths as $path) {
                        Storage::disk('public')->delete($path);
                    }
                } elseif ($image->type === 'tech_passport') {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete(); // Yozuvni o‘chirish
            }

            // Asosiy vehicle yozuvini o‘chirish
            $vehicle->delete();

            $messages = [
                'uz' => 'Transport vositasi va rasmlari o‘chirildi.',
                'ru' => 'Транспортное средство и изображения удалены.',
                'en' => 'Vehicle and its images deleted successfully.',
            ];

            $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ], 200);
        } catch (\Exception $e) {

            $messages = [
                'uz' => 'O‘chirishda xatolik yuz berdi.',
                'ru' => 'Ошибка при удалении.',
                'en' => 'Failed to delete.',
            ];

            $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

            return response()->json([
                'status' => 'error',
                'message' => $message,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
