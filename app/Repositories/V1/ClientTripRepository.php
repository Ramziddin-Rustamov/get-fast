<?php

namespace App\Repositories\V1;

use App\Http\Resources\V1\ClientTripResource;
use App\Http\Resources\V1\ClientTripWithMoreInfoResource;
use App\Http\Resources\V1\CompetedInProgressCanceledTripsForClientsResources;
use App\Models\V1\Trip;
use Illuminate\Support\Carbon;

use function Symfony\Component\Clock\now;

class ClientTripRepository
{


    public function getAllTrips()
    {
        try {
            $trips =  Trip::whereIn('status', ['active', 'full'])->paginate(10);
            return ClientTripResource::collection($trips);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'messsage ' => " Hozircha trip yo'q",
            ], 404);
        }
    }

    // tested
    public function getTripById($id)
    {
            $booking = Trip::whereHas('bookings', function ($q) use ($id) {
                $q->where('user_id', auth()->id())->where('id', $id);
            })
                ->orderBy('id', 'asc')
                ->first();

            $lang =  auth()->user()->authLanguage->language ?? 'uz';

            $messages = [
                'not_found' => [
                    'uz' => 'Safar topilmadi',
                    'en' => 'Trip not found',
                    'ru' => 'Поездка не найдена',
                ],
                'success' => [
                    'uz' => 'Safar muvaffaqiyatli olindi',
                    'en' => 'Trip fetched successfully',
                    'ru' => 'Поездка успешно получена',
                ],
            ];

            if (is_null($booking)) {
                return response()->json([
                    'status' => 'error',
                    'message' => $messages['not_found'][$lang] ?? $messages['not_found']['uz'],
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => $messages['success'][$lang] ?? $messages['success']['uz'],
                'data' => new ClientTripWithMoreInfoResource($booking),
            ]);
        
    }
    // tested
    public function canceledTripsForClient()
    {
        try {

            $language = auth()->user()->authLanguage->language ?? 'uz';

            $cancelledTrips = Trip::whereHas('bookings', function ($q) {
                $q->where('user_id', auth()->id())
                    ->where('status', 'cancelled');
            })
                ->orderBy('id', 'desc')
                ->paginate(10);

            if ($cancelledTrips->isEmpty()) {

                $messages = [
                    'uz' => 'Sizda bekor qilingan sayohatlar yo‘q',
                    'ru' => 'У вас нет отменённых поездок',
                    'en' => 'You have no cancelled trips',
                ];

                return response()->json([
                    'status' => 'success',
                    'message' => $messages[$language] ?? $messages['uz'],
                    'data' => null
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => [
                    'uz' => 'Bekor qilingan sayohatlar',
                    'ru' => 'Отменённые поездки',
                    'en' => 'Cancelled trips',
                ][$language] ?? 'Cancelled trips',
                'data' => CompetedInProgressCanceledTripsForClientsResources::collection($cancelledTrips),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // tested 
    public function getInprogressTripsForClient()
    {
        try {

            $now = now(); // Carbon object

            $inProgressTrips = Trip::whereHas('bookings', function ($q) {
                $q->where('user_id', auth()->id())
                    ->where('status', 'confirmed');
            })
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->orderBy('start_time', 'asc')
                ->paginate(10);

            $messagesNot = [
                'uz' => 'Sizda davom etayotgan sayohatlar yo‘q',
                'ru' => 'У вас нет поездок в процессе',
                'en' => 'You have no in progress trips',
            ];

            if ($inProgressTrips->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $messagesNot[auth()->user()->authLanguage->language] ?? $messagesNot['uz'],
                    'data' => null
                ], 200);
            }

            $messageSuccess = [
                'uz' => 'Sizda davom etayotgan sayohatlar mavjud',
                'ru' => 'У вас есть поездки в процессе',
                'en' => 'You have in progress trips',
            ];

            return response()->json([
                'status' => 'success',
                'message' => $messageSuccess[auth()->user()->authLanguage->language] ?? $messageSuccess['uz'],
                'data' => CompetedInProgressCanceledTripsForClientsResources::collection($inProgressTrips),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // tested
    public function getCompletedTripsForClient()
    {
        try {
            $now = Carbon::now();
            $completedTrips = Trip::whereHas('bookings', function ($q) {
                $q->where('user_id', auth()->id())
                    ->where('status', 'completed');
            })
                ->orderBy('id', 'asc')
                ->paginate(10);


            if (count($completedTrips) == 0) {
                $messages = [
                    'uz' => 'Sizda yakunlangan sayohatlar yo‘q',
                    'ru' => 'У вас нет завершённых поездок',
                    'en' => 'You have no completed trips',
                ];

                $message = $messages[auth()->user()->authLanguage->language] ?? $messages['uz'];

                return response()->json([
                    'status' => 'success',
                    'message' => $message,
                    'data' => null
                ], 200);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Sizda yakunlangan sayohatlar mavjud',
                'data' => CompetedInProgressCanceledTripsForClientsResources::collection($completedTrips)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => "Something went wrong " . $e
            ], 404);
        }
    }
}
