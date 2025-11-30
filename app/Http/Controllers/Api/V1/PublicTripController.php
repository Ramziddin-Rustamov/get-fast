<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PublicTripResource;
use App\Http\Resources\V1\PublicTripWithLessInfoResource;
use App\Models\V1\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicTripController extends Controller
{
    protected function getUserLang()
    {
        return auth()->user()->authLanguage->language ?? 'uz';
    }

    public function getTripsWithLessInfo()
    {
        $userLang = $this->getUserLang();
        $trips = Trip::whereIn('status', ['active', 'full'])->paginate(20);

        $messages = [
            'uz' => 'Safarlar muvaffaqiyatli olindi',
            'ru' => 'Поездки успешно получены',
            'en' => 'Trips fetched successfully',
        ];

        return response()->json([
            'status' => 'success',
            'message' => $messages[$userLang] ?? $messages['uz'],
            'data' => PublicTripWithLessInfoResource::collection($trips),
            'meta' => [
                'current_page' => $trips->currentPage(),
                'last_page' => $trips->lastPage(),
                'per_page' => $trips->perPage(),
                'total' => $trips->total(),
            ]
        ], 200);
    }

    public function search(Request $request)
    {
        $userLang = $this->getUserLang();

        $from = $request->query('start_quarter_id');
        $to = $request->query('end_quarter_id');
        $departureDate = $request->query('departure_date');
        $returnDate = $request->query('return_date');
        $isRoundTrip = $request->query('is_round_trip');

        // Departure leg
        $departureTrips = Trip::where('start_quarter_id', $from)
            ->where('end_quarter_id', $to)
            ->where('status', 'active')
            ->where('start_time', '>=', Carbon::parse($departureDate))
            ->where('available_seats', '>', 0)
            ->get();

        $returnTrips = collect();

        if ($isRoundTrip && $returnDate) {
            $returnTrips = Trip::where('start_quarter_id', $to)
                ->where('end_quarter_id', $from)
                ->where('status', 'active')
                ->where('start_time', '>=', Carbon::parse($returnDate))
                ->where('available_seats', '>', 0)
                ->get();
        }

        $messages = [
            'uz' => 'Qidiruv natijalari muvaffaqiyatli olindi',
            'ru' => 'Результаты поиска успешно получены',
            'en' => 'Search results fetched successfully',
        ];

        return response()->json([
            'status' => 'success',
            'message' => $messages[$userLang] ?? $messages['uz'],
            'data' => [
                'departure_trips' => PublicTripResource::collection($departureTrips),
                'return_trips' => PublicTripResource::collection($returnTrips),
            ]
        ], 200);
    }

    public function getAllTripsForPublic()
    {
        $userLang = $this->getUserLang();
        $trips = Trip::whereIn('status', ['active', 'full'])->paginate(20);

        $messages = [
            'uz' => 'Safarlar muvaffaqiyatli olindi',
            'ru' => 'Поездки успешно получены',
            'en' => 'Trips fetched successfully',
        ];

        return response()->json([
            'status' => 'success',
            'message' => $messages[$userLang] ?? $messages['uz'],
            'data' => PublicTripResource::collection($trips),
            'meta' => [
                'current_page' => $trips->currentPage(),
                'last_page' => $trips->lastPage(),
                'per_page' => $trips->perPage(),
                'total' => $trips->total(),
            ]
        ], 200);
    }

    public function getTripByIdForPublic($id)
    {
        $userLang = $this->getUserLang();
        $trip = Trip::find($id);

        if (is_null($trip)) {
            $messages = [
                'uz' => 'Safar topilmadi.',
                'ru' => 'Поездка не найдена.',
                'en' => 'Trip not found.',
            ];

            return response()->json([
                'status' => 'error',
                'message' => $messages[$userLang] ?? $messages['uz'],
                'data' => null
            ], 404);
        }

        $messages = [
            'uz' => 'Safar muvaffaqiyatli olindi',
            'ru' => 'Поездка успешно получена',
            'en' => 'Trip fetched successfully',
        ];

        return response()->json([
            'status' => 'success',
            'message' => $messages[$userLang] ?? $messages['uz'],
            'data' => new PublicTripResource($trip)
        ], 200);
    }
}
