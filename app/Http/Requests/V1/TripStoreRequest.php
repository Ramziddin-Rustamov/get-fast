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
        return true;
    }

    
    public function rules()
    {
        return [
            'driver_id' => 'required|exists:users,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_location' => 'required|string',
            'end_location' => 'required|string',
            'start_time' => ['required', 'date', function ($attribute, $value, $fail) {
            $start_time = Carbon::parse($value);
            $now = Carbon::now();
            $limit = $now->addHours(48);
                if ($start_time->lessThan($now) || $start_time->greaterThan($limit)) {
                    $fail('Start time must be within the next 48 hours.');
                }
            }],
            'end_time' => 'nullable|date',
            'price_per_seat' => 'required|numeric|min:0',
            'total_seats' => 'required|integer|min:1',
            'available_seats' => 'required|integer',
        ];
    }
}
