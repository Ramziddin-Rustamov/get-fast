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
            return response()->json($this->errorResponse[$this->language], 404);
        }
    }

    public function getTripById($id)
    {
        try {
            $booking = Trip::whereHas('bookings', function ($q) use ($id) {
                $q->where('user_id', auth()->id())->where('id', $id);
            })
                ->orderBy('id', 'asc')
                ->first();

            if (is_null($booking)) {
                return response()->json($this->errorResponse[$this->language], 404);
            }

            return new ClientTripWithMoreInfoResource($booking);
        } catch (\Exception $e) {
            return response()->json($this->errorResponse[$this->language], 404);
        }
    }

    public function canceledTripsForClient()
    {
        try {
            // ✅ Cancelled
            $cancelledTrips = Trip::whereHas('bookings', function ($q) {
                $q->where('user_id', auth()->id())
                    ->where('status', 'cancelled');
            })
                // ->orWhere(function ($q) {
                //     $q->whereIn('status', ['cancelled'])
                //         ->whereHas('bookings', function ($q2) {
                //             $q2->where('user_id', auth()->id());
                //         });
                // })
                ->orderBy('id', 'asc')
                ->paginate(10);

            if (count($cancelledTrips) == 0) {
                $messages = [
                    'uz' => 'Sizda bekor qilingan sayohatlar yo‘q',
                    'ru' => 'У вас нет отменённых поездок',
                    'en' => 'You have no cancelled trips',
                ];

                $message = $messages[$this->language];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 404);
            }
            return CompetedInProgressCanceledTripsForClientsResources::collection($cancelledTrips);
        } catch (\Exception $e) {
            return response()->json($this->errorResponse[$this->language], 404);
        }
    }

    public function getInprogressTripsForClient()
    {
        try {
            
            $now = Carbon::now();
            return $now;
            $inProgressTrips = Trip::whereHas('bookings', function ($q) {
                $q->where('user_id', auth()->id())
                    ->where('status', 'confirmed');
            })
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->where('status', '!=', 'cancelled')
                ->orderBy('id', 'asc')
                ->paginate(10);

            $messagesNot = [
                'uz' => 'Sizda davom etayotgan sayohatlar yo‘q',
                'ru' => 'У вас нет поездок в процессе',
                'en' => 'You have no in progress trips',
            ];

            $message = $messagesNot[auth()->user()->authLanguage->language] ?? $messagesNot['uz'];

            if (count($inProgressTrips) == 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                    'data' => null

                ], 404);
            }

            $messageSuccess = [
                'uz' => 'Sizda davom etayotgan sayohatlar mavjud',
                'ru' => 'У вас есть поездки в процессе',
                'en' => 'You have in progress trips',
            ];

            return [
                'status' => 'success',
                'message' => $messageSuccess[auth()->user()->authLanguage->language] ?? $messageSuccess['uz'],
                'data' => CompetedInProgressCanceledTripsForClientsResources::collection($inProgressTrips),
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => "Something went wrong " . $e
            ], 404);
        }
    }


    public function getCompletedTripsForClient()
    {
        try {
            $now = Carbon::now();
            $completedTrips = Trip::whereHas('bookings', function ($q) {
                $q->where('user_id', auth()->id())
                    ->where('status', 'completed');
            })
                // ->orWhere(function ($q) {
                //     $q->where('status', 'completed')
                //         ->whereHas('bookings', function ($q2) {
                //             $q2->where('user_id', auth()->id());
                //         });
                // })
                ->orderBy('id', 'asc')
                ->paginate(10);


            if (count($completedTrips) == 0) {
                $messages = [
                    'uz' => 'Sizda yakunlangan sayohatlar yo‘q',
                    'ru' => 'У вас нет завершённых поездок',
                    'en' => 'You have no completed trips',
                ];

                $message = $messages[$this->language];

                return response()->json([
                    'status' => 'error',
                    'message' => $message
                ], 404);
            }
            return CompetedInProgressCanceledTripsForClientsResources::collection($completedTrips);
        } catch (\Exception $e) {
            return response()->json($this->errorResponse[$this->language], 404);
        }
    }
}
