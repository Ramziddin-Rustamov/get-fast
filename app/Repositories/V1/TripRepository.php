<?php

namespace App\Repositories\V1;

use App\Models\V1\Trip;
use App\Http\Resources\V1\TripResource;
use App\Models\V1\Point;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TripRepository
{

    public $errorResponse = [
        'status' => 'error',
        "message" => "Not found !"
    ];

    public $successResponse = [
        'status' => 'seccess',
        "message" => "Deleted successsfully !"
    ];

    public function getAllTrips()
    {
        return Trip::whereIn('status', ['active', 'completed'])->andWhere('driver_id', auth()->user()->id)->paginate(20);
    }

    public function getTripById($id)
    {
        $trip  =  Trip::where('driver_id', auth()->user()->id)->find($id);
        if (is_null($trip) && empty($trip)) {
            return response()->json($this->errorResponse, 404);
        }
        return response()->json(new TripResource($trip), 200);
    }
    public function createTrip(array $data)
    {
        try {
            DB::beginTransaction();

            $startPoint = Point::create([
                'lat' => $data['start_lat'],
                'long' => $data['start_long'],
            ]);

            $endPoint = Point::create([
                'lat' => $data['end_lat'],
                'long' => $data['end_long'],
            ]);

            $trip = new Trip();
            $trip->driver_id = auth()->user()->id;
            $trip->vehicle_id = $data['vehicle_id'];
            $trip->start_quarter_id = $data['start_quarter_id'];
            $trip->end_quarter_id = $data['end_quarter_id'];
            $trip->start_time = $data['start_time'];
            $trip->end_time = $data['end_time'];
            $trip->price_per_seat = $data['price_per_seat'];
            $trip->total_seats = (int) $data['total_seats'];
            $trip->available_seats = $data['available_seats'];
            $trip->start_point_id = $startPoint->id;
            $trip->end_point_id = $endPoint->id;
            $trip->save();

            DB::commit();

            return response()->json(new TripResource($trip), 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Trip yaratishda xatolik yuz berdi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateTrip($id, array $data)
    {
        try {
            DB::beginTransaction();

            $trip = Trip::find($id);
            if (!$trip) {
                return response()->json([
                    'message' => 'Trip topilmadi.',
                    'error' => 'error'
                ], 404);
            }

            $startPoint = Point::find($trip->start_point_id);
            if (!$startPoint) {
                return response()->json([
                    'message' => 'Start point topilmadi.',
                    'error' => 'error'
                ], 404);
            }

            $endPoint = Point::find($trip->end_point_id);
            if (!$endPoint) {
                return response()->json([
                    'message' => 'End point topilmadi.',
                    'error' => 'error'
                ], 404);
            }

            // Start point yangilash
            $startPoint->update([
                'lat' => $data['start_lat'] ?? $startPoint->lat,
                'long' => $data['start_long'] ?? $startPoint->long,
            ]);

            // End point yangilash
            $endPoint->update([
                'lat' => $data['end_lat'] ?? $endPoint->lat,
                'long' => $data['end_long'] ?? $endPoint->long,
            ]);

            // Trip yangilash
            $trip->vehicle_id = $data['vehicle_id'];
            $trip->start_quarter_id = $data['start_quarter_id'];
            $trip->end_quarter_id = $data['end_quarter_id'];
            $trip->start_time = $data['start_time'];
            $trip->end_time = $data['end_time'];
            $trip->price_per_seat = $data['price_per_seat'];
            $trip->total_seats = (int) $data['total_seats'];
            $trip->available_seats = $data['available_seats'];
            $trip->save();

            DB::commit();

            return response()->json(new TripResource($trip), 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Trip yangilashda xatolik: ' . $e->getMessage(), [
                'trip_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Trip yangilashda xatolik yuz berdi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function deleteTrip($id)
    {
        $trip = Trip::where('driver_id', auth()->user()->id)->find($id);
        if (is_null($trip) && empty($trip)) {
            return response()->json($this->errorResponse, 404);
        }
        $trip->delete();
        return response()->json($this->successResponse, 200);
    }
}
