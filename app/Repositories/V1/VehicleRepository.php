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

    public $errorResponse = [
        'status' => 'error',
        "message" => "Not found !"
    ];

    public $successResponse = [
        'status' => 'seccess',
        "message" => "Deleted successsfully !"
    ];

    public function getAll()
    {
        return VehicleResource::collection(Vehicle::where('user_id', Auth::user()->id)->get());
    }

    public function findById($id)
    {
        $vehicle = Vehicle::where('user_id', Auth::user()->id)->find($id);
        if (is_null($vehicle) && empty($vehicle)) {
            return response()->json($this->errorResponse, 404);
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

                // car_images
                if ($request->hasFile('car_images')) {
                    $paths = [];

                    foreach ($request->file('car_images') as $image) {
                        $path = $image->store('vehicles/cars/' . Auth::id(), 'public');
                        $paths[] = $path;
                    }

                    $vehicleImage = new VehicleImages();
                    $vehicleImage->vehicle_id = $vehicle->id;
                    $vehicleImage->image_path = json_encode($paths);
                    $vehicleImage->type = 'vehicle';
                    $vehicleImage->save();
                }

                // tech_passport
                if ($request->hasFile('tech_passport')) {
                    $filename = time() . '.' . $request->file('tech_passport')->getClientOriginalExtension();
                    $path = 'drivers/tech_passports/' . Auth::id();
                    $path_for_tech_passport = $request->file('tech_passport')->storeAs($path, $filename, 'public');

                    $vehicleImage = new VehicleImages();
                    $vehicleImage->vehicle_id = $vehicle->id;
                    $vehicleImage->image_path = $path_for_tech_passport;
                    $vehicleImage->type = 'tech_passport';
                    $vehicleImage->save();
                }

                return $vehicle;
            });

            return response()->json(new VehicleResource($vehicle), 201);
        } catch (\Exception $e) {
            Log::error('Vehicle creation failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Xatolik yuz berdi. Iltimos, keyinroq urinib ko‘ring.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // public function update(Request $request, $id)
    // {

    //     $request->validate([
    //         'vehicle_number' => 'required|string|unique:vehicles,car_number',
    //         'tech_passport_number' => 'required|string|unique:vehicles,tech_passport_number',
    //         'car_model' => 'required|string',
    //         'car_color_id' => 'required|exists:colors,id',
    //         'seats' => 'required|integer|min:1|max:8',
    //         'car_images' => 'sometimes|array|min:1',
    //         'car_images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:44024',
    //         'tech_passport' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2448',
    //     ]);

    //     try {
    //         $vehicle = DB::transaction(function () use ($request, $id) {
    //             $data = $request->validated();

    //             $vehicle = Vehicle::findOrFail($id);
    //             $vehicle->color_id = $data['car_color_id'];
    //             $vehicle->model = $data['car_model'];
    //             $vehicle->tech_passport_number = $data['tech_passport_number'];
    //             $vehicle->car_number = $data['vehicle_number'];
    //             $vehicle->seats = $data['seats'];
    //             $vehicle->save();

    //             // Eskilarni o'chirish (car_images)
    //             if ($request->hasFile('car_images')) {
    //                 $oldImages = VehicleImages::where('vehicle_id', $vehicle->id)
    //                     ->where('type', 'vehicle')
    //                     ->get();

    //                 foreach ($oldImages as $image) {
    //                     $paths = json_decode($image->image_path, true);
    //                     foreach ($paths as $oldPath) {
    //                         Storage::disk('public')->delete($oldPath);
    //                     }
    //                     $image->delete();
    //                 }

    //                 $paths = [];
    //                 foreach ($request->file('car_images') as $image) {
    //                     $path = $image->store('vehicles/cars/' . Auth::id(), 'public');
    //                     $paths[] = $path;
    //                 }

    //                 $vehicleImage = new VehicleImages();
    //                 $vehicleImage->vehicle_id = $vehicle->id;
    //                 $vehicleImage->image_path = json_encode($paths);
    //                 $vehicleImage->type = 'vehicle';
    //                 $vehicleImage->save();
    //             }

    //             // tech_passport yangilash
    //             if ($request->hasFile('tech_passport')) {
    //                 $oldTech = VehicleImages::where('vehicle_id', $vehicle->id)
    //                     ->where('type', 'tech_passport')
    //                     ->first();

    //                 if ($oldTech) {
    //                     Storage::disk('public')->delete($oldTech->image_path);
    //                     $oldTech->delete();
    //                 }

    //                 $filename = time() . '.' . $request->file('tech_passport')->getClientOriginalExtension();
    //                 $path = 'drivers/tech_passports/' . Auth::id();
    //                 $path_for_tech_passport = $request->file('tech_passport')->storeAs($path, $filename, 'public');

    //                 $techImage = new VehicleImages();
    //                 $techImage->vehicle_id = $vehicle->id;
    //                 $techImage->image_path = $path_for_tech_passport;
    //                 $techImage->type = 'tech_passport';
    //                 $techImage->save();
    //             }

    //             return $vehicle;
    //         });

    //         return response()->json(new VehicleResource($vehicle), 200);
    //     } catch (\Exception $e) {
    //         Log::error('Vehicle update failed: ' . $e->getMessage(), [
    //             'vehicle_id' => $id,
    //             'user_id' => Auth::id(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Ma\'lumotni yangilashda xatolik yuz berdi.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function delete($id)
    {
        try {
            $vehicle = Vehicle::where('user_id', Auth::id())->find($id);
    
            if (is_null($vehicle)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transport vositasi topilmadi.'
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
    
            return response()->json([
                'status' => 'success',
                'message' => 'Transport vositasi va rasmlari o‘chirildi.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Vehicle delete failed: ' . $e->getMessage(), [
                'vehicle_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'status' => 'error',
                'message' => 'O‘chirishda xatolik yuz berdi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}
