<?php

namespace App\Repositories\V1;

use App\Models\V1\Trip;
use App\Http\Resources\V1\TripResource;
use App\Models\V1\Point;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TripRepository
{
    public $errorMessages = [
        'uz' => [
            'status' => 'error',
            'message' => 'Trip topilmadi.'
        ],
        'ru' => [
            'status' => 'error',
            'message' => 'Поездка не найдена.'
        ],
        'en' => [
            'status' => 'error',
            'message' => 'Trip not found.'
        ],
    ];



     public $successMessages = [
        'uz' => [
            'status' => 'success',
            'message' => 'Trip muvaffaqiyatli o‘chirildi.'
        ],
        'ru' => [
            'status' => 'success',
            'message' => 'Поездка успешно удалена.'
        ],
        'en' => [
            'status' => 'success',
            'message' => 'Trip deleted successfully.'
        ],
    ];

    public function getAllTrips()
    {
        return Trip::whereIn('status', ['active', 'completed'])->andWhere('driver_id', auth()->user()->id)->paginate(20);
    }

    public function getTripById($id)
    {
        $trip = Trip::where('driver_id', auth()->user()->id)->find($id);

        if (!$trip) {
            $lang = auth()->user()->authLanguage->language ?? 'uz';
            return response()->json($this->errorMessages[$lang], 404);
        }

        return response()->json($trip, 200);
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

            return response()->json($trip, 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            $messages = [
                'uz' => 'Sizning poezdka yaratishda xatolik yuz berdi.',
                'ru' => 'Ошибка при создании поездки.',
                'en' => 'Error occurred while creating trip.',
            ];

            $lang = auth()->user()->authLanguage->language ?? 'uz';

            return response()->json([
                'status' => 'error',
                'message' => $messages[$lang],
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateTrip($id, array $data)
    {
        try {
            DB::beginTransaction();

            $trip = Trip::find($id);
            $messages = [
                'uz' => 'Trip topilmadi.',
                'ru' => 'Поездка не найдена.',
                'en' => 'Trip not found.',
            ];

            $lang = auth()->user()->authLanguage->language ?? 'uz';

            if (!$trip) {
                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$lang],
                    'error' => 'error'
                ], 404);
            }


            $messages = [
                'uz' => 'Start point topilmadi.',
                'ru' => 'Начальная точка не найдена.',
                'en' => 'Start point not found.',
            ];

            $lang = auth()->user()->authLanguage->language ?? 'uz';

            $startPoint = Point::find($trip->start_point_id);
            if (!$startPoint) {
                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$lang],
                    'error' => 'error'
                ], 404);
            }


            $messages = [
                'uz' => 'End point topilmadi.',
                'ru' => 'Конечная точка не найдена.',
                'en' => 'End point not found.',
            ];

            $lang = auth()->user()->authLanguage->language ?? 'uz';

            $endPoint = Point::find($trip->end_point_id);
            if (!$endPoint) {
                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$lang],
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

            return response()->json($trip, 200);
        } catch (\Throwable $e) {
            DB::rollBack();


            $messages = [
                'uz' => 'Trip yangilashda xatolik yuz berdi.',
                'ru' => 'Ошибка при обновлении поездки.',
                'en' => 'Error occurred while updating trip.',
            ];

            $lang = auth()->user()->authLanguage->language ?? 'uz';

            return response()->json([
                'status' => 'error',
                'message' => $messages[$lang],
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function deleteTrip($id)
    {
        $lang = auth()->user()->authLanguage->language ?? 'uz';

      
        
        $trip = Trip::where('driver_id', auth()->user()->id)->find($id);
        if (!$trip) {
            return response()->json($this->errorMessages[$lang], 404);
        }
        
        $trip->delete();
        return response()->json($this->successMessages[$lang], 200);
        
    }
}
