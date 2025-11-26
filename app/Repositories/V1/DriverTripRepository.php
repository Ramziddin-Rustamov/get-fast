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
use App\Models\V1\CompanyBalance;
use App\Models\V1\CompanyBalanceTransaction;

class DriverTripRepository
{


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
            $messages = [
                'uz' => 'Safar topilmadi',
                'ru' => 'Поездка не найдена',
                'en' => 'Trip not found',
            ];

            $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 404);
        }
        return new DriverTripResource($trip);
    }


    public function createTrip($request)
    {
        try {

            if ($request->validated()) {
                $data = $request->validated();
            }

            // Duplicate check
            $existingTrip = Trip::where('driver_id', auth()->id())
                ->where('vehicle_id', $data['vehicle_id'])
                ->where('start_quarter_id', $data['start_quarter_id'])
                ->where('end_quarter_id', $data['end_quarter_id'])
                ->where('start_time', $data['start_time'])
                ->where('end_time', $data['end_time'])
                ->first();

            if ($existingTrip) {
                $messages = [
                    'uz' => 'Bu ma’lumotlar bilan avval yuborilgan poezdka topildi',
                    'ru' => 'По этим данным уже была создана поездка',
                    'en' => 'A trip with this information has already been submitted',
                ];

                $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 409);
            }


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
            $trip->start_region_id = $data['start_region_id'];
            $trip->end_region_id = $data['end_region_id'];
            $trip->start_district_id = $data['start_district_id'];
            $trip->end_district_id = $data['end_district_id'];
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

            $messages = [
                'uz' => 'Trip yaratishda xatolik yuz berdi.',
                'ru' => 'Ошибка при создании поездки.',
                'en' => 'Error occurred while creating the trip.',
            ];

            $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

            return response()->json([
                'status' => 'error',
                'message' => $message . ' ' . $e->getMessage(),
                'error' => $e->getMessage(),
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
                $messages = [
                    'uz' => 'Trip topilmadi.',
                    'ru' => 'Поездка не найдена.',
                    'en' => 'Trip not found.',
                ];

                $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                    'error' => 'error',
                ], 404);
            }

            $startPoint = Point::find($trip->start_point_id);
            if (!$startPoint) {
                $messages = [
                    'uz' => 'Start point topilmadi.',
                    'ru' => 'Стартовая точка не найдена.',
                    'en' => 'Start point not found.',
                ];

                $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                    'error' => 'error',
                ], 404);
            }

            $endPoint = Point::find($trip->end_point_id);
            if (!$endPoint) {
                $messages = [
                    'uz' => 'End point topilmadi.',
                    'ru' => 'Конечная точка не найдена.',
                    'en' => 'End point not found.',
                ];

                $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                    'error' => 'error',
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

            if ($data['total_seats'] < $trip->available_seats) {
                $messages = [
                    'uz' => 'Umumiy o‘rindiqlar sonidan bo‘sh o‘rindiqlar soni kichik bo‘lishi mumkin emas.',
                    'ru' => 'Количество свободных мест не может быть меньше общего числа мест.',
                    'en' => 'The number of empty seats cannot be less than the total seats.',
                ];

                $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                    'error' => 'error',
                ], 400);
            }

            // Trip yangilash
            $trip->vehicle_id = $data['vehicle_id'];
            $trip->start_quarter_id = $data['start_quarter_id'];
            $trip->end_quarter_id = $data['end_quarter_id'];
            $trip->start_region_id = $data['start_region_id'];
            $trip->end_region_id = $data['end_region_id'];
            $trip->start_district_id = $data['start_district_id'];
            $trip->end_district_id = $data['end_district_id'];
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
            $messages = [
                'uz' => 'Trip yangilashda xatolik yuz berdi.',
                'ru' => 'Ошибка при обновлении поездки.',
                'en' => 'An error occurred while updating the trip.',
            ];

            $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

            return response()->json([
                'status' => 'error',
                'message' => $message,
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function cancel($id)
    {
        try {

            DB::beginTransaction();
            $trip = Trip::where('driver_id', auth()->user()->id)->find($id);

            if (!$trip) {
                $messages = [
                    'uz' => 'Trip topilmadi.',
                    'ru' => 'Поездка не найдена.',
                    'en' => 'Trip not found.',
                ];

                $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 404);
            }

            if ($trip->status == 'cancelled') {
                $messages = [
                    'uz' => 'Trip allaqachon bekor qilingan.',
                    'ru' => 'Поездка уже отменена.',
                    'en' => 'Trip already cancelled.',
                ];

                $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 400);
            }



            $trip->status = 'cancelled';
            $trip->expired_at = now();
            $trip->save();

            // Expired trips ga ko‘chirish
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

            // Foizlar
            $companyPercent = env('SERVICE_FEE_FOR_DRIVERS_TO_CANCEL_TRIP', 4);
            $clientCompensationPercent = env('REFOUND_COMPENSATION_FOR_CLIENTS', 1);


            $bookings = Booking::where('trip_id', $trip->id)->get();
            foreach ($bookings as $booking) {

                // Driver va Company balanslari
                $driver = $trip->driver;
                $driverBefore = $trip->driver->balance->balance;

                $companyBalance = CompanyBalance::first();
                $companyBefore = $companyBalance->balance;

                // Shu tripga tegishli barcha bookinglar


                $client = User::find($booking->user_id);
                $totalPrice = $booking->total_price;

                $clientCompensation = $totalPrice * ($clientCompensationPercent / 100); // 50000 * 0.01 = 500
                $companyAmount = $totalPrice * ($companyPercent / 100); // 50000 * 0.04 = 2000


                $driverWithdrawal = $totalPrice - ($clientCompensation + $companyAmount); // 50000 - ( 500 + 2000) = 50000
                $driverPaidForCompanyAndClient = $clientCompensation + $companyAmount; // 500 + 2000 = 2500
                $paidForClient  = $totalPrice + $clientCompensation; // 50000 + 500 = 50500

                $driverReasons = [
                    'uz' => "Sayohatni bekor qildingiz. Sizning haydovchi balansingizdan $driverWithdrawal so‘m yechildi. Kompaniya esa $driverPaidForCompanyAndClient so‘mni qayta ishladi va mijozlarga oldingi to‘lovlarini qaytarib berdi. Shuningdek, sizdan mijoz uchun qo‘shimcha kompensatsiya sifatida $clientCompensation va kompaniya uchun $companyAmount so‘m yechildi.",
                    'ru' => "Вы отменили поездку. С вашего водительского баланса было списано $driverWithdrawal сум. Компания обработала $driverPaidForCompanyAndClient сум и вернула клиентам их предыдущие платежи. Также с вас была удержана дополнительная компенсация клиенту в размере $clientCompensation и компании в размере $companyAmount сум.",
                    'en' => "You cancelled the trip. An amount of $driverWithdrawal was deducted from your driver balance. The company processed $driverPaidForCompanyAndClient and refunded the clients their previous payments. Additionally, an extra  client compensation of $clientCompensation was deducted from you. The company received $companyAmount.",
                ];

                $driverAfter = ($driverBefore - ($driverWithdrawal + $clientCompensation + $companyAmount)); // 142500 - (47500 + 500 + 2000) = 92500 



                $a = ($driverWithdrawal + $clientCompensation + $companyAmount); // 47500 - 500 + 2000 = 50000
                // Driver transaction
                BalanceTransaction::create([
                    'user_id' => $driver->id,
                    'type' => 'debit',
                    'amount' => $a,
                    'balance_before' => $driverBefore,
                    'balance_after' => $driverAfter,
                    'trip_id' => $trip->id,
                    'status' => 'success',
                    'reason' => $driverReasons[$driver->authLanguage->language ?? 'uz'],
                    'reference_id' => $booking->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $driver->balance->update(['balance' => $driverAfter]);

                $clientReasons = [
                    'uz' => "Sayohat haydovchi tomonidan bekor qilindi. Haydovchi sizga $totalPrice so'mni qaytarib berdi va qo'shimcha $clientCompensation so'm kompensatsiya to'lab berdi.",
                    'ru' => "Поездка была отменена водителем. Водитель вернул вам $totalPrice сум и дополнительно выплатил компенсацию в размере $clientCompensation сум.",
                    'en' => "The trip was cancelled by the driver. The driver refunded you $totalPrice UZS and additionally paid $clientCompensation UZS as compensation.",
                ];
                

                BalanceTransaction::create([
                    'user_id' => $booking->user_id,
                    'type' => 'credit',
                    'amount' => $totalPrice + $clientCompensation,
                    'balance_before' => $client->myBalance->balance,
                    'balance_after' => $client->myBalance->balance + ($totalPrice + $clientCompensation),
                    'trip_id' => $trip->id,
                    'status' => 'success',
                    'reason' => $clientReasons[$client->authLanguage->language ?? 'uz'],
                    'reference_id' => $booking->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $booking->user->balance->update(['balance' => $client->balance->balance + ($totalPrice + $clientCompensation)]);


                $companyBalance = CompanyBalance::first();
                    $companyReason = [
                        'uz' => "Haydovchi tomonidan sayohat bekor qilindi. Kompaniyaga $companyAmount so'm qaytarildi.",
                    ];


                CompanyBalanceTransaction::create([
                    'company_balance_id' => $companyBalance->id,
                    'amount' => $companyAmount,
                    'balance_before' => $companyBalance->balance,
                    'balance_after' => $companyBalance->balance - $companyAmount,
                    'trip_id' => $trip->id,
                    'booking_id' => $booking->id,
                    'status' => 'success',
                    'type' => 'debit',
                    'reason' => '',
                    'currency' => 'UZS',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $companyBalance->update([
                    'balance' => $companyBalance->balance - $companyAmount
                ]);









                // // Driver transaction
                // BalanceTransaction::create([
                //     'user_id' => $driver->id,
                //     'type' => 'debit',
                //     'amount' => $driverAmount,
                //     'balance_before' => $driverBefore,
                //     'balance_after' => $driverAfter,
                //     'trip_id' => $trip->id,
                //     'status' => 'success',
                //     'reason' => $driverReasons[$driver->authLanguage->language ?? 'uz'],
                //     'reference_id' => $booking->id,
                //     'created_at' => now(),
                //     'updated_at' => now(),
                // ]);

                // $driverBefore = $driverAfter; // keyingi client uchun update

                // // Client balansini yangilash
                // $clientBalance = $client->myBalance;
                // $clientBefore = $clientBalance->balance;
                // $clientAfter = $clientBefore + $totalPrice;
                // $clientBalance->update(['balance' => $clientAfter]);

                // // Client transaction yozish
                // $reasons = [
                //     'uz' => "Trip bekor qilindi. Qaytarilgan summa: $totalPrice + Kompensatsiya: $clientCompensation",
                //     'ru' => "Поездка отменена. Возврат: $totalPrice + Компенсация: $clientCompensation",
                //     'en' => "Trip cancelled. Refund: $totalPrice + Compensation: $clientCompensation",
                // ];
                // $reason = $reasons[auth()->user()->authLanguage->language ?? 'uz'];


                // // Client transaction
                // BalanceTransaction::create([
                //     'user_id' => $client->id,
                //     'type' => 'credit',
                //     'amount' => $totalPrice,
                //     'balance_before' => $clientBefore,
                //     'balance_after' => $clientAfter,
                //     'trip_id' => $trip->id,
                //     'status' => 'success',
                //     'reason' => $reason,
                //     'reference_id' => $booking->id,
                //     'created_at' => now(),
                //     'updated_at' => now(),
                // ]);

                // // Company balansini yangilash
                // $companyAfter = $companyBefore + $companyAmount;
                // $companyBalance->update(['balance' => $companyAfter]);

                // $companyReasons = [
                //     'uz' => "Trip bekor qilindi. Company ulushi $companyPercent%: $companyAmount, Booking ID: $booking->id, Driver: $driver->name, Client: $client->name",
                //     'ru' => "Поездка отменена. Доля компании $companyPercent%: $companyAmount, Booking ID: $booking->id, Водитель: $driver->name, Клиент: $client->name",
                //     'en' => "Trip cancelled. Company share $companyPercent%: $companyAmount, Booking ID: $booking->id, Driver: $driver->name, Client: $client->name",
                // ];

                // // Company transaction
                // CompanyBalanceTransaction::create([
                //     'company_balance_id' => $companyBalance->id,
                //     'amount' => $companyAmount,
                //     'balance_before' => $companyBefore,
                //     'balance_after' => $companyAfter,
                //     'trip_id' => $trip->id,
                //     'booking_id' => $booking->id,
                //     'type' => 'income',
                //     'reason' => $companyReasons['uz'],
                //     'currency' => 'UZS',
                //     'created_at' => now(),
                //     'updated_at' => now(),
                // ]);

                // $companyBefore = $companyAfter;
                // $trip->delete();
            }
            DB::commit();

            $messages = [
                'uz' => 'Trip bekor qilindi va mablag‘lar qaytarildi.',
                'ru' => 'Поездка отменена и возвраты обработаны успешно.',
                'en' => 'Trip cancelled and refunds processed successfully.',
            ];

            $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            $messages = [
                'uz' => 'Xatolik yuz berdi.',
                'ru' => 'Произошла ошибка.',
                'en' => 'An error occurred.',
            ];

            $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

            return response()->json([
                'status' => 'error',
                'message' => $message,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
