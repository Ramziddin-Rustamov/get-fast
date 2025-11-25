<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DriverExpiredTripsResource;
use App\Models\V1\ExpiredTrip;

class DriverExpiredTripsControllerApi extends Controller
{

    public $language;

    public function __construct()
    {
        $this->language = auth()->user()->authLanguage->language ?? 'uz';
    }

    public function getExpeiredTrips()
    {
        $expiredTrips = ExpiredTrip::where('driver_id', auth()->user()->id)->paginate(20);

        if (count($expiredTrips) == 0 || !$expiredTrips) {
            $messages = [
                'uz' => 'Muddati o‘tgan sayohatlar topilmadi',
                'ru' => 'Истёкшие поездки не найдены',
                'en' => 'No expired trips found',
            ];

            return response()->json([
                'status' => 'error',
                'message' => $messages[$this->language]
            ], 404);
        }

        return DriverExpiredTripsResource::collection($expiredTrips);
    }

    public function getExpiredTrip($id)
    {
        $trip = ExpiredTrip::where('driver_id', auth()->user()->id)->find($id);

        if (!$trip) {
            $messages = [
                'uz' => 'Muddati o‘tgan sayohat topilmadi',
                'ru' => 'Истёкшая поездка не найдена',
                'en' => 'No expired trip found',
            ];

            return response()->json([
                'status' => 'error',
                'message' => $messages[$this->language]
            ], 404);
        }

        return response()->json(new DriverExpiredTripsResource($trip), 200);
    }
}
