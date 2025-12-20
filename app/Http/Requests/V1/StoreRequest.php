<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'vehicle_number' => 'required|string|unique:vehicles,car_number',
            'car_model' => 'required|string',
            'car_color_id' => 'required|exists:colors,id',
            'tech_passport_number' => 'required|string|unique:vehicles,tech_passport_number',
            'seats' => 'required|integer|min:1|max:8',
        ];
    }
}
