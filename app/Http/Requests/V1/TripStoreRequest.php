<?php

namespace App\Http\Requests\V1;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class TripStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_quarter_id' => 'required|string|exists:quarters,id',
            'end_quarter_id' => 'required|string|exists:quarters,id',
            'end_time' => ['nullable', 'date', function ($attribute, $value, $fail) {
                if (!request()->has('start_time')) {
                    $fail('Start time is required for validating end time.');
                    return;
                }
                $start_time = Carbon::parse(request()->input('start_time'));
                $end_time = Carbon::parse($value);
    
                $diffInMinutes = $start_time->diffInMinutes($end_time, false); // false: negative values if end_time < start_time
    
                if ($diffInMinutes < 10) {
                    $fail('The time difference between start time and end time must be at least 10 minutes.');
                }
                if ($diffInMinutes > 48 * 60) {
                    $fail('The time difference between start time and end time must not exceed 48 hours.');
                }
                if ($diffInMinutes <= 0) {
                    $fail('End time must be after start time.');
                }
            }],
            'price_per_seat' => 'required|numeric|min:0',
            'total_seats' => 'required|integer|min:1',
            'available_seats' => 'required|integer',
            'start_lat' => 'nullable|numeric|between:-90,90',
            'start_long' => 'nullable|numeric|between:-180,180',
            'end_lat' => 'nullable|numeric|between:-90,90',
            'end_long' => 'nullable|numeric|between:-180,180',
            'start_time' => ['required', 'date', function ($attribute, $value, $fail) {
                $start_time = Carbon::parse($value);
                $now = Carbon::now();
                $limit = $now->copy()->addHours(48);
                if ($start_time->lessThan($now) || $start_time->greaterThan($limit)) {
                    $fail('Start time must be within the next 48 hours.');
                }
            }],
        ];
    }
    
}
