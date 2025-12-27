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

        $response = [
            'uz' => 'Safarlar ro\'yhati',
            'ru' => 'Список поездок',
            'en' => 'Trip list',
        ];

        return response()->json([
            'status' => 'success',
            'message' => $response[auth()->user()->authLanguage->language ?? 'uz'],
            'data' => DriverTripResource::collection($activeTrips),
            'meta' => [
                'current_page' => $activeTrips->currentPage(),
                'last_page' => $activeTrips->lastPage(),
                'per_page' => $activeTrips->perPage(),
                'total' => $activeTrips->total(),
            ]
        ], 200);
    }

    public function getTripById($id)
    {
        $trip = Trip::where('driver_id', auth()->user()->id)
            ->find($id);

        if (is_null($trip)) {
            $messages = [
                'uz' => 'Safar topilmadi',
                'ru' => 'Поездка не найдена',
                'en' => 'Trip not found',
            ];

            $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

            return response()->json([
                'status' => 'error',
                'message' => $message,
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Trip fetched successfully',
            'data' => new DriverTripResource($trip)
        ], 200);
    }

    public function createTrip($request)
    {
        try {
            $data = $request->validated();

            // Duplicate check
            $existingTrip = Trip::where('driver_id', auth()->id())
                ->where('vehicle_id', $data['vehicle_id'])
                ->where('start_quarter_id', $data['start_quarter_id'])
                ->where('end_quarter_id', $data['end_quarter_id'])
                ->where('start_time', $data['start_time'])
                ->where('end_time', $data['end_time'])
                ->whereIn('status', ['pending', 'active'])
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
                    'data' => null
                ], 409);
            }

            DB::beginTransaction();

            // Create start and end points
            $startPoint = Point::create([
                'lat' => $data['start_lat'],
                'long' => $data['start_long'],
            ]);

            $endPoint = Point::create([
                'lat' => $data['end_lat'],
                'long' => $data['end_long'],
            ]);

            // Create trip
            $trip = Trip::create([
                'driver_id' => auth()->user()->id,
                'vehicle_id' => $data['vehicle_id'],
                'start_quarter_id' => $data['start_quarter_id'],
                'end_quarter_id' => $data['end_quarter_id'],
                'start_region_id' => $data['start_region_id'],
                'end_region_id' => $data['end_region_id'],
                'start_district_id' => $data['start_district_id'],
                'end_district_id' => $data['end_district_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'price_per_seat' => $data['price_per_seat'],
                'available_seats' => $data['available_seats'],
                'expired_at' => $data['end_time'],
                'start_point_id' => $startPoint->id,
                'end_point_id' => $endPoint->id,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Trip successfully created',
                'data' => new DriverTripResource($trip)
            ], 201);
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
                'message' => $message,
                'data' => null,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancel($id)
    {
        try {


            DB::beginTransaction();

            $trip = Trip::where('driver_id', auth()->id())->find($id);

            if (!$trip) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Trip not found'
                    ],
                    404
                );
            }

            if ($trip->status === 'cancelled') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Trip already cancelled'
                ], 400);
            }

            // Trip-ni bekor qilish
            $trip->update(['status' => 'cancelled', 'expired_at' => now()]);

            // Expired-ga yozish
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
                'expired_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            // FOIZLAR
            $companyPercent = env('SERVICE_FEE_FOR_DRIVERS_TO_CANCEL_TRIP', 4); // 4%
            $clientCompensationPercent = env('REFOUND_COMPENSATION_FOR_CLIENTS', 1); // 1 %

            $driver = $trip->driver;
            $companyBalance = CompanyBalance::lockForUpdate()->firstOrFail();


            foreach ($trip->bookings as $booking) {

                $client = $booking->user;
                $totalPrice = $booking->total_price;

                // Hisoblash
                $clientCompensation = $totalPrice * ($clientCompensationPercent / 100); // 400000 * 0.01% = 4000
                $companyFee = $totalPrice * ($companyPercent / 100); // 400000 * 0.04% = 16000
                $overallCompensation = $clientCompensation + $companyFee;

                // Driverdan olinadigan pul
                $driverDeductionOnDocs = $totalPrice + $clientCompensation + $companyFee; // 400000 + 4000+ 16000 = 420.000
                // 400000 + 4000 + 16000 = 420000
                // --- DRIVER BALANCE UPDATE ---
                $driverBefore = $driver->balance->balance;
                $driverAfter = ($driverBefore + $companyFee + $clientCompensation) - $totalPrice;
                // (400000 + 16000 + 4000 )- 400000 = 0 
                $amount = ($totalPrice - ($companyFee + $clientCompensation));
                $driverGotBeforeCancel = $totalPrice - ($companyFee + $clientCompensation);
                $reasonDriver = [
                    'uz' => "
                        Siz sayohatni bekor qildingiz. 
                        Bekor qilinmasdan oldin olishingiz kerak bo‘lgan summa: $driverGotBeforeCancel so‘m.
                
                        Umumiy tushum: $totalPrice so‘m edi. 
                        Sayohatni sotib olgan mijozga sizning hisobingizdan $clientCompensation so‘m kompensatsiya berildi.
                
                        Xizmatni bekor qilganingiz uchun kompaniya tomonidan $companyFee so‘m ushlab qolindi.
                
                        Avval to‘langan foizlar sizga qaytarildi: $overallCompensation so‘m.
                        Yakunda sizdan ushlab qolinadigan umumiy summa: $driverDeductionOnDocs so‘m.
                    ",
                
                    'ru' => "
                        Вы отменили поездку.
                        Сумма, которую вы должны были получить до отмены: $driverGotBeforeCancel сум.
                
                        Общий доход составлял: $totalPrice сум.
                        Клиенту, купившему поездку, была предоставлена компенсация в размере $clientCompensation сум за ваш счёт.
                
                        За отмену услуги с вас удержано комиссией компании: $companyFee сум.
                
                        Ранее удержанные проценты были возвращены вам: $overallCompensation сум.
                        Итоговая сумма удержаний с вас составляет: $driverDeductionOnDocs сум.
                    ",
                
                    'en' => "
                        You have cancelled the trip.
                        The amount you were supposed to receive before cancellation: $driverGotBeforeCancel UZS.
                
                        The total revenue of the trip was: $totalPrice UZS.
                        The customer who purchased the trip received a compensation of $clientCompensation UZS, taken from your balance.
                
                        A company fee of $companyFee UZS was deducted for cancelling the service.
                
                        Previously deducted percentages have been refunded to you: $overallCompensation UZS.
                        The final amount deducted from you is: $driverDeductionOnDocs UZS.
                    ",
                ];
                


                BalanceTransaction::create([
                    'user_id' => $driver->id,
                    'type' => 'debit',
                    'amount' => $amount, // 380000
                    'balance_before' => $driverBefore, // 380000
                    'balance_after' => $driverAfter, // 0
                    'trip_id' => $trip->id,
                    'reference_id' => $booking->id,
                    'status' => 'success',
                    'reason' => $reasonDriver[$driver->authLanguage->language ?? 'uz'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $driver->balance->update(['balance' => $driverAfter]);

                $driverBefore = $driver->balance->balance;
                // 0 = 0
                $driverAfter = $driverBefore - $overallCompensation;
                // (0 - 20000) = -20000
                $resonCompensation = [
                    'uz' => "Siz safarni bekor qilganingiz uchun $companyFee so‘m kompaniya to‘lovi va $clientCompensation so‘m mijoz kompensatsiyasi sizning hisobingizdan ushlab qolindi.",
                    
                    'ru' => "За отмену поездки с вашего счёта были удержаны $companyFee сум комиссии компании и $clientCompensation сум компенсации клиенту.",
                    
                    'en' => "For cancelling the trip, $companyFee UZS company fee and $clientCompensation UZS client compensation have been deducted from your account.",
                ];
                
                BalanceTransaction::create([
                    'user_id' => $driver->id,
                    'type' => 'debit',
                    'amount' => $overallCompensation, // 20000  
                    'balance_before' => $driverBefore, // 0
                    'balance_after' => $driverAfter, // -20000
                    'trip_id' => $trip->id,
                    'reference_id' => $booking->id,
                    'status' => 'success',
                    'reason' => $resonCompensation[$driver->authLanguage->language ?? 'uz'],
                    'created_at' => now()->addMinutes(1),
                    'updated_at' => now()->addMinutes(1),
                ]);

                $driver->balance->update([
                    'balance' => $driverAfter,
                    'updated_at' => now()->addMinutes(1),
                    'created_at' => now()->addMinutes(1),
                ]);

                $companyBefore = $companyBalance->balance;
                $companyAfter = $companyBefore - ($companyFee + $clientCompensation);

                $companyReason = [
                    'uz' => "Haydovhi $driver->first_name ( $driver->phone) safarni ( {{$trip->startQuarter->name}} dan {{$trip->endQuarter->name}} ) safarni bekor qildi
                    va kampaniya oldin olgan $overallCompensation so‘mni haydavchiga qaytardi va haydavchidan qolgan summa mijozga o`ndirildi.",
                ];

                CompanyBalanceTransaction::create([
                    'company_balance_id' => $companyBalance->id,
                    'amount' => ($companyFee + $clientCompensation),
                    'balance_before' => $companyBefore,
                    'balance_after' => $companyAfter,
                    'trip_id' => $trip->id,
                    'booking_id' => $booking->id,
                    'type' => 'outgoing',
                    'status' => 'success',
                    'reason' => $companyReason['uz'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $companyBalance->update(['balance' => $companyAfter]);




                // --- CLIENT BALANCE UPDATE ---
                $refundToClient = $totalPrice + $clientCompensation;

                $clientBefore = $client->balance->balance;
                $clientAfter = $clientBefore + $refundToClient;

                $reasonClient = [
                    'uz' => "Sizni safaringiz bekor qilindi. Sizga to`liq pul qaytarildi,( $totalPrice so`m) va kompensatsiya qaytarildi, ($clientCompensation so`m).",
                    'ru' => "Ваша поездка была отменена. Вы получите полную сумму, ($totalPrice so`m), а также компенсация, ($clientCompensation so`m).",
                    'en' => "Your trip was canceled. You will receive the full amount, ($totalPrice so`m), and compensation, ($clientCompensation so`m).",
                ];

                BalanceTransaction::create([
                    'user_id' => $client->id,
                    'type' => 'credit',
                    'amount' => $refundToClient,
                    'balance_before' => $clientBefore,
                    'balance_after' => $clientAfter,
                    'trip_id' => $trip->id,
                    'reference_id' => $booking->id,
                    'status' => 'success',
                    'reason' => $reasonClient[$trip->user->authLanguage->language ?? 'uz']
                ]);

                $client->balance->update([
                    'balance' => $clientAfter,
                    'updated_at' => now(),
                    'created_at' => now()
                ]);


                // --- COMPANY BALANCE UPDATE ---
                $companyBefore = $companyBalance->balance;
                $companyAfter = $companyBefore + $companyFee;

                $comReason = [
                    'uz' => "Haydovhi $driver->first_name ( $driver->phone) safarni ( {{$trip->startQuarter->name}} dan {{$trip->endQuarter->name}} ) safarni bekor qildi
                     va kampaniyaga $companyFee so‘m qaytardi.",
                ];

                CompanyBalanceTransaction::create([
                    'company_balance_id' => $companyBalance->id,
                    'amount' => $companyFee,
                    'balance_before' => $companyBefore,
                    'balance_after' => $companyAfter,
                    'trip_id' => $trip->id,
                    'booking_id' => $booking->id,
                    'type' => 'income',
                    'status' => 'success',
                    'reason' => $comReason['uz'],
                    'created_at' => now()->addMinutes(1),
                    'updated_at' => now()->addMinutes(1),
                ]);

                $companyBalance->update([
                    'balance' => $companyAfter,
                    'updated_at' => now()->addMinutes(1),
                    'created_at' => now()->addMinutes(1),
                ]);
                $booking->update(['status' => 'cancelled']);
            }
            DB::commit();

            $response = [
                'uz' => 'Sayohat bekor qilindi va barcha qaytarishlar muvaffaqiyatli yakunlandi.',
                'ru' => 'Поездка отменена, и все возвраты успешно завершены.',
                'en' => 'Trip cancelled and all refunds completed successfully.'
            ];

            return response()->json([
                'status' => 'success',
                'message' => $response[auth()->user()->authLanguage->language ?? 'uz'],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCanceledTrips()
    {
        $userLang = auth()->user()->authLanguage->language ?? 'uz';

        $canceledTrips = Trip::where('driver_id', auth()->user()->id)
            ->where('status', 'cancelled')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        // Tilga mos message
        if ($canceledTrips->isEmpty()) {
            $messages = [
                'uz' => 'Hozircha bekor qilingan safarlar mavjud emas',
                'ru' => 'Пока нет отмененных поездок',
                'en' => 'There are no canceled trips at the moment',
            ];

            return response()->json([
                'status' => 'error',
                'message' => $messages[$userLang] ?? $messages['uz'],
                'data' => [],
                'meta' => [
                    'current_page' => $canceledTrips->currentPage(),
                    'last_page' => $canceledTrips->lastPage(),
                    'per_page' => $canceledTrips->perPage(),
                    'total' => $canceledTrips->total(),
                ]
            ], 200);
        }

        $messages = [
            'uz' => 'Bekor qilingan safarlar muvaffaqiyatli olindi',
            'ru' => 'Отмененные поездки успешно получены',
            'en' => 'Canceled trips fetched successfully',
        ];

        return response()->json([
            'status' => 'success',
            'message' => $messages[$userLang] ?? $messages['uz'],
            'data' => DriverTripResource::collection($canceledTrips),
            'meta' => [
                'current_page' => $canceledTrips->currentPage(),
                'last_page' => $canceledTrips->lastPage(),
                'per_page' => $canceledTrips->perPage(),
                'total' => $canceledTrips->total(),
            ]
        ], 200);
    }

    public function getActiveTrips()
    {
        $userLang = auth()->user()->authLanguage->language ?? 'uz';

        $activeTrips = Trip::where('driver_id', auth()->user()->id)
            ->where('status', 'active')
            ->where('end_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->paginate(10);

        // Tilga mos message
        if ($activeTrips->isEmpty()) {
            $messages = [
                'uz' => 'Hozircha faol safarlar mavjud emas',
                'ru' => 'Пока нет активных поездок',
                'en' => 'There are no active trips at the moment',
            ];

            return response()->json([
                'status' => 'error',
                'message' => $messages[$userLang] ?? $messages['uz'],
                'data' => [],
                'meta' => [
                    'current_page' => $activeTrips->currentPage(),
                    'last_page' => $activeTrips->lastPage(),
                    'per_page' => $activeTrips->perPage(),
                    'total' => $activeTrips->total(),
                ]
            ], 200);
        }

        $messages = [
            'uz' => 'Faol safarlar muvaffaqiyatli olindi',
            'ru' => 'Активные поездки успешно получены',
            'en' => 'Active trips fetched successfully',
        ];

        return response()->json([
            'status' => 'success',
            'message' => $messages[$userLang] ?? $messages['uz'],
            'data' => DriverTripResource::collection($activeTrips),
            'meta' => [
                'current_page' => $activeTrips->currentPage(),
                'last_page' => $activeTrips->lastPage(),
                'per_page' => $activeTrips->perPage(),
                'total' => $activeTrips->total(),
            ]
        ], 200);
    }

    public function getCompletedTrips()
    {
        $userLang = auth()->user()->authLanguage->language ?? 'uz';

        $completedTrips = Trip::where('driver_id', auth()->user()->id)
            ->where('status', 'completed')
            ->orderBy('end_time', 'desc')
            ->paginate(10);

        // Tilga mos message
        if ($completedTrips->isEmpty()) {
            $messages = [
                'uz' => 'Hozircha yakunlangan safarlar mavjud emas',
                'ru' => 'Пока нет завершенных поездок',
                'en' => 'There are no completed trips at the moment',
            ];

            return response()->json([
                'status' => 'error',
                'message' => $messages[$userLang] ?? $messages['uz'],
                'data' => [],
                'meta' => [
                    'current_page' => $completedTrips->currentPage(),
                    'last_page' => $completedTrips->lastPage(),
                    'per_page' => $completedTrips->perPage(),
                    'total' => $completedTrips->total(),
                ]
            ], 200);
        }

        $messages = [
            'uz' => 'Yakunlangan safarlar muvaffaqiyatli olindi',
            'ru' => 'Завершенные поездки успешно получены',
            'en' => 'Completed trips fetched successfully',
        ];

        return response()->json([
            'status' => 'success',
            'message' => $messages[$userLang] ?? $messages['uz'],
            'data' => DriverTripResource::collection($completedTrips),
            'meta' => [
                'current_page' => $completedTrips->currentPage(),
                'last_page' => $completedTrips->lastPage(),
                'per_page' => $completedTrips->perPage(),
                'total' => $completedTrips->total(),
            ]
        ], 200);
    }


    
}
