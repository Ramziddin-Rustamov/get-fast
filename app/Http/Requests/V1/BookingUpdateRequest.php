<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class BookingUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'trip_id' => 'sometimes|exists:trips,id',
            'user_id' => 'sometimes|exists:users,id',
            'seats_booked' => 'sometimes|integer|min:1',
        ];
    }
}
