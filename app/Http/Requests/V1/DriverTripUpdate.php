<?php

namespace App\Http\Requests\V1;

use Illuminate\Support\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class DriverTripUpdate extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role === 'driver';
    }

    protected function failedAuthorization()
    {
        abort(response()->json([
            'message' => 'Only drivers are allowed to perform this action.',
            'status' => 403,
        ], 403));
    }

    public function rules()
    {
        return [

            'vehicle_id' => [
                'sometimes',
                'integer',
                'exists:vehicles,id',
            ],

            'start_quarter_id' => [
                'sometimes',
                'integer',
                'exists:quarters,id',
            ],

            'end_quarter_id' => [
                'sometimes',
                'integer',
                'exists:quarters,id',
            ],

            'start_region_id' => [
                'sometimes',
                'integer',
                'exists:regions,id',
            ],

            'end_region_id' => [
                'sometimes',
                'integer',
                'exists:regions,id',
            ],

            'start_district_id' => [
                'sometimes',
                'integer',
                'exists:districts,id',
            ],

            'end_district_id' => [
                'sometimes',
                'integer',
                'exists:districts,id',
            ],

            'start_time' => [
                'sometimes',
                'date',
                function ($attribute, $value, $fail) {

                    $startTime = Carbon::parse($value);
                    $now = Carbon::now();
                    $limit = $now->copy()->addHours(48);

                    if ($startTime->lessThan($now)) {
                        $fail('Start time must be in the future.');
                        return;
                    }

                    if ($startTime->greaterThan($limit)) {
                        $fail('Start time must be within the next 48 hours.');
                    }
                },
            ],

            'end_time' => [
                'sometimes',
                'date',
                function ($attribute, $value, $fail) {

                    /*
                     * Agar start_time ham update qilinayotgan bo'lsa,
                     * yangi start_time bilan tekshiramiz.
                     *
                     * Agar start_time yuborilmagan bo'lsa,
                     * controller eski trip start_time bilan tekshirishi kerak.
                     */
                    if (request()->has('start_time')) {

                        $startTime = Carbon::parse(
                            request()->input('start_time')
                        );

                        $endTime = Carbon::parse($value);

                        $diffInMinutes = $startTime->diffInMinutes(
                            $endTime,
                            false
                        );

                        if ($diffInMinutes <= 0) {
                            $fail('End time must be after start time.');
                            return;
                        }

                        if ($diffInMinutes < 10) {
                            $fail(
                                'The time difference between start time and end time must be at least 10 minutes.'
                            );
                            return;
                        }

                        if ($diffInMinutes > 48 * 60) {
                            $fail(
                                'The time difference between start time and end time must not exceed 48 hours.'
                            );
                        }
                    }
                },
            ],

            'price_per_seat' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            /*
             * Bu field sizning holatingizda EDIT qilinadi.
             *
             * Chunki driver offline passenger olishi mumkin.
             *
             * 4 -> 3 -> 2 -> 1
             */
            'available_seats' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'start_lat' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'start_long' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'end_lat' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'end_long' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:-180,180',
            ],
        ];
    }
}
