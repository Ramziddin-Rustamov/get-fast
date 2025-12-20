<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PublicTripResource;
use App\Http\Resources\V1\PublicTripWithLessInfoResource;
use App\Models\V1\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

        $startRegion  = $request->query('start_region_id');
        $endRegion    = $request->query('end_region_id');
        $startDistrict = $request->query('start_district_id');
        $endDistrict   = $request->query('end_district_id');
        $startQuarter  = $request->query('start_quarter_id');
        $endQuarter    = $request->query('end_quarter_id');

        $departureDate = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $request->query('departure_date'),
        );

        // Natija:
        $departureDate = $departureDate->format('Y-m-d H:i:s'); //2025-05-24 20:49:12 example


        // --- 1-BOSQICH: quarter → quarter ---
        $departureTrips = Trip::where('start_quarter_id', $startQuarter)
            ->where('end_quarter_id', $endQuarter)
            ->where('start_time', '>=', $departureDate)
            ->whereIn('status', ['active', 'full'])
            ->paginate(20);

        // Agar topilgan bo‘lsa to‘g‘ridan to‘g‘ri qaytariladi
        if ($departureTrips->isEmpty()) {

            // --- 2-BOSQICH: district → quarter ---
            $departureTrips = Trip::where('start_district_id', $startDistrict)
                ->where('end_quarter_id', $endQuarter)
                ->where('start_time', '>=', $departureDate)
                ->whereIn('status', ['active', 'full'])
                ->paginate(20);

            // Agar district → quarter bo'yicha ham bo‘lmasa
            if ($departureTrips->isEmpty()) {

                // --- Region → quarter ---
                $departureTrips = Trip::where('start_region_id', $startRegion)
                    ->where('end_quarter_id', $endQuarter)
                    ->where('start_time', '>=', $departureDate)
                    ->whereIn('status', ['active', 'full'])
                    ->paginate(20);
            }

            // --- 3-BOSQICH: district → district ---
            if ($departureTrips->isEmpty()) {
                $departureTrips = Trip::where('start_district_id', $startDistrict)
                    ->where('end_district_id', $endDistrict)
                    ->where('start_time', '>=', $departureDate)
                    ->whereIn('status', ['active', 'full'])
                    ->paginate(20);
            }
        }

        // ROUND TRIP
        $returnTrips = collect();
        if ($request->query('is_round_trip') && $request->query('return_date')) {
            $returnTrips = Trip::where('start_quarter_id', $endQuarter)
                ->where('end_quarter_id', $startQuarter)
                ->where('start_time', '>=', Carbon::parse($request->query('return_date')))
                ->whereIn('status', ['active', 'full'])
                ->paginate(20);
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

        // Cache kaliti
        $cacheKey = "public_trips_page_" . request()->query('page', 1);

        // Cache 20 sekund
        $trips = Cache::remember($cacheKey, 20, function () {
            return Trip::whereIn('status', ['active', 'full'])
                ->paginate(20);
        });

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
