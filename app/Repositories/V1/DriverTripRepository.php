<?php

namespace App\Repositories\V1;

use App\Http\Resources\V1\DriverTripResource;
use App\Models\BalanceTransaction;
use App\Models\User;
use App\Models\V1\Booking;
use App\Models\V1\Trip;
use App\Models\V1\Point;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverTripRepository
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


        $activeTrips = Trip::where('driver_id', auth()->user()->id)
            ->where('end_time', '>=', now())
            ->paginate(10);

        return DriverTripResource::collection($activeTrips);
    }



    public function getTripById($id)
    {

        $trip = Trip::where('driver_id', auth()->user()->id)->where('end_time', '>=', now())
            ->find($id);


        if (is_null($trip) && empty($trip)) {
            return response()->json($this->errorResponse, 404);
        }
        return new DriverTripResource($trip);
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
            $trip->available_seats = $data['available_seats'];
            $trip->expired_at = $data['end_time'];
            $trip->start_point_id = $startPoint->id;
            $trip->end_point_id = $endPoint->id;
            $trip->save();

            DB::commit();

            return response()->json(new DriverTripResource($trip), 200);
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

            $trip = Trip::where('id', $id)
                ->where('driver_id', auth()->user()->id)
                ->first();
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

            return response()->json(new DriverTripResource($trip), 200);
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


    public function cancel($id)
    {
        $trip = Trip::findOrFail($id);


        if ($trip->status == 'cancelled') {
            return response()->json(
                [
                    'message' => 'Trip already cancelled',
                    'status' => 'error'
                ],
                400
            );
        }

        DB::transaction(function () use ($trip) {
            $trip->status = 'cancelled';
            $trip->expired_at = now();
            $trip->save();

            // Agar expired_trips jadvali bo‘lsa, unga ko‘chirish
            DB::table('expired_trips')->insert([
                'driver_id' => $trip->driver_id,
                'vehicle_id' => $trip->vehicle_id,
                'start_point_id' => $trip->start_point_id,
                'end_point_id' => $trip->end_point_id,
                'start_quarter_id' => $trip->start_quarter_id,
                'end_quarter_id' => $trip->end_quarter_id,
                'start_time' => $trip->start_time,
                'end_time' => $trip->end_time,
                'price_per_seat' => $trip->price_per_seat,
                'total_seats' => $trip->total_seats,
                'available_seats' => $trip->available_seats,
                'status' => 'cancelled',
                'expired_at' => $trip->expired_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Shu tripga tegishli barcha bookinglar
            $bookings = Booking::where('trip_id', $trip->id)->get();

            foreach ($bookings as $booking) {
                $userId = $booking->user_id;
                $user = User::find($userId);
                $totalPrice = $booking->total_price;

                // Driver o‘zi cancel qilganmi?
                if ($userId == $trip->driver_id) {
                    // 15% jarima, 85% qaytariladi
                    $penalty = $totalPrice * 0.15;
                    $refund = $totalPrice - $penalty;
                } else {
                    // 100% refund
                    $refund = $totalPrice;
                    $penalty = 0;
                }

                // Balansni yangilash (user_balances)
                $balance = $user->myBalance;
                $before = $balance->balance;
                $after = $before + $refund;

                $balance->update([
                    'balance' => $after,
                ]);

                // balance_transactions jadvaliga yozish
                BalanceTransaction::create([
                    'user_id' => $userId,
                    'type' => 'credit',
                    'amount' => $refund,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'trip_id' => $trip->id,
                    'status' => 'success',
                    'reason' => $userId == $trip->driver_id
                        ? "Booking canceled - 85% refund, 15% penalty"
                        : "Booking canceled - full refund",
                    'reference_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return response()->json([
            'message' => 'Trip cancelled and refunds processed successfully.',
            'status' => 'success'
        ]);
    }
}
