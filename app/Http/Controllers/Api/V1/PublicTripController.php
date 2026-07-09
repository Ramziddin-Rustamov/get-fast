<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PublicTripResource;
use App\Http\Resources\V1\PublicTripWithLessInfoResource;
use App\Models\SearchLog;
use App\Models\V1\District;
use App\Models\V1\Quarter;
use App\Models\V1\Region;
use App\Models\V1\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PublicTripController extends Controller
{
    protected function getUserLang()
    {
        return auth()->user()->authLanguage->language ?? 'uz';
    }

    public function getTripsWithLessInfo()
    {
        $userLang = $this->getUserLang();
        $trips = Trip::whereIn('status', ['active', 'full'])
            ->with('parcels.types') // pochta ma'lumoti (N+1 dan qochish)
            ->latest() // created_at bo‘yicha tartiblash
            ->paginate(20);

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

    $startRegion   = $request->query('start_region_id');
    $endRegion     = $request->query('end_region_id');
    $startDistrict = $request->query('start_district_id');
    $endDistrict   = $request->query('end_district_id');
    $startQuarter  = $request->query('start_quarter_id');
    $endQuarter    = $request->query('end_quarter_id');

    $departureDate = $request->query('departure_date');

    if ($departureDate) {
        $departureDate = Carbon::parse($departureDate)->format('Y-m-d H:i:s');
    }

    // helper closure (DRY qilish uchun)
    $applyDateFilter = function ($query) use ($departureDate) {
        if ($departureDate) {
            $query->where('start_time', '>=', $departureDate);
        }
        return $query;
    };

    // --- 1-BOSQICH: quarter → quarter ---
    $departureTrips = $applyDateFilter(
        Trip::where('start_quarter_id', $startQuarter)
            ->where('end_quarter_id', $endQuarter)
            ->whereIn('status', ['active', 'full'])
    )->paginate(20);

    if ($departureTrips->isEmpty()) {

        // --- 2-BOSQICH: district → quarter ---
        $departureTrips = $applyDateFilter(
            Trip::where('start_district_id', $startDistrict)
                ->where('end_quarter_id', $endQuarter)
                ->whereIn('status', ['active', 'full'])
        )->paginate(20);

        if ($departureTrips->isEmpty()) {

            // --- Region → quarter ---
            $departureTrips = $applyDateFilter(
                Trip::where('start_region_id', $startRegion)
                    ->where('end_quarter_id', $endQuarter)
                    ->whereIn('status', ['active', 'full'])
            )->paginate(20);
        }

        // --- 3-BOSQICH: district → district ---
        if ($departureTrips->isEmpty()) {
            $departureTrips = $applyDateFilter(
                Trip::where('start_district_id', $startDistrict)
                    ->where('end_district_id', $endDistrict)
                    ->whereIn('status', ['active', 'full'])
            )->paginate(20);
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

    // Pochta ma'lumotini yuklab olamiz (N+1 dan qochish)
    $departureTrips->getCollection()->load('parcels.types');
    if (method_exists($returnTrips, 'getCollection')) {
        $returnTrips->getCollection()->load('parcels.types');
    }

    // Marketing uchun qidiruvni bazaga saqlaymiz (qidiruvni buzmasligi uchun try/catch)
    $this->logSearch($request, $departureTrips->total());

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

    /**
     * Foydalanuvchining qidiruvini (qayerdan-qayerga) marketing uchun saqlaydi.
     */
    protected function logSearch(Request $request, int $resultsCount): void
    {
        try {
            $startRegion   = $request->query('start_region_id');
            $endRegion     = $request->query('end_region_id');
            $startDistrict = $request->query('start_district_id');
            $endDistrict   = $request->query('end_district_id');
            $startQuarter  = $request->query('start_quarter_id');
            $endQuarter    = $request->query('end_quarter_id');

            // Kamida bitta manzil parametri bo'lmasa saqlamaymiz
            if (! $startRegion && ! $startDistrict && ! $startQuarter
                && ! $endRegion && ! $endDistrict && ! $endQuarter) {
                return;
            }

            $departureDate = $request->query('departure_date');
            $returnDate    = $request->query('return_date');

            SearchLog::create([
                'user_id'           => auth()->id(),
                'start_region_id'   => $startRegion,
                'start_district_id' => $startDistrict,
                'start_quarter_id'  => $startQuarter,
                'end_region_id'     => $endRegion,
                'end_district_id'   => $endDistrict,
                'end_quarter_id'    => $endQuarter,
                'start_location'    => $this->resolveLocationName($startQuarter, $startDistrict, $startRegion),
                'end_location'      => $this->resolveLocationName($endQuarter, $endDistrict, $endRegion),
                'departure_date'    => $departureDate ? Carbon::parse($departureDate) : null,
                'is_round_trip'     => (bool) $request->query('is_round_trip'),
                'return_date'       => $returnDate ? Carbon::parse($returnDate) : null,
                'results_count'     => $resultsCount,
                'ip_address'        => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            // Log saqlash qidiruvni to'xtatmasligi kerak
            Log::warning('Search log saqlanmadi: ' . $e->getMessage());
        }
    }

    /**
     * Eng aniq manzil nomini qaytaradi (mahalla > tuman > viloyat).
     */
    protected function resolveLocationName($quarterId, $districtId, $regionId): ?string
    {
        if ($quarterId && $quarter = Quarter::find($quarterId)) {
            return $quarter->name;
        }

        if ($districtId && $district = District::find($districtId)) {
            return $district->name_uz;
        }

        if ($regionId && $region = Region::find($regionId)) {
            return $region->name_uz;
        }

        return null;
    }


    public function getTripByRegionToRegion(Request $request)
    {
        $userLang = $this->getUserLang();

        $startRegionId = $request->input('start_region_id');
        $endRegionId = $request->input('end_region_id');

        $trips = Trip::where(function ($query) use ($startRegionId, $endRegionId) {
            // borish
            $query->where('start_region_id', $startRegionId)
                ->where('end_region_id', $endRegionId);
        })
            ->orWhere(function ($query) use ($startRegionId, $endRegionId) {
                // kelish
                $query->where('start_region_id', $endRegionId)
                    ->where('end_region_id', $startRegionId);
            })
            ->whereIn('status', ['active', 'full'])
            ->with('parcels.types')
            ->paginate(20);

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

    public function getAllTripsForPublic()
    {
        $userLang = $this->getUserLang();

        // Cache kaliti
        $cacheKey = "public_trips_page_" . request()->query('page', 1);

        // Cache 20 sekund
        $trips = Cache::remember($cacheKey, 20, function () {
            return Trip::whereIn('status', ['active', 'full'])
                ->with('parcels.types')
                ->latest('created_at')
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
        $trip = Trip::with('parcels.types')->find($id);

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
