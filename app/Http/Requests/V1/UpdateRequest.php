<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'vehicle_number' => 'required|string|unique:vehicles,car_number,' . $this->route('id'),
            'tech_passport_number' => 'required|string|unique:vehicles,tech_passport_number,' . $this->route('id'),
            'car_model' => 'required|string',
            'car_color_id' => 'required|exists:colors,id',
            'seats' => 'required|integer|min:1|max:8',
            'car_images' => 'sometimes|array|min:1',
            'car_images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:44024',
            'tech_passport' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2448',
        ];
    }
}
