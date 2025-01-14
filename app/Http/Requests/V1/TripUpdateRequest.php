<?php

namespace App\Http\Requests\V1;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class TripUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'driver_id' => 'sometimes|exists:users,id',
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'start_location' => 'sometimes|string',
            'end_location' => 'sometimes|string',
            'start_time' => ['required', 'date', function ($attribute, $value, $fail) {
            $start_time = Carbon::parse($value);
            $now = Carbon::now();
            $limit = $now->addHours(48);
                if ($start_time->lessThan($now) || $start_time->greaterThan($limit)) {
                    $fail('Start time must be within the next 48 hours.');
                }
            }],
            'end_time' => 'nullable|date',
            'price_per_seat' => 'sometimes|numeric|min:0',
            'total_seats' => 'sometimes|integer|min:1',
            'available_seats' => 'sometimes|integer|min:1',
        ];
    }
}
