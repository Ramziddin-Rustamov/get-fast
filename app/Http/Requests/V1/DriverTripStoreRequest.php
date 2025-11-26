<?php

namespace App\Http\Requests\V1;

use Illuminate\Support\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DriverTripStoreRequest extends FormRequest
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

        $messages = [
            'uz' => "Faqat haydovchilar ushbu amalni bajarishi mumkin.",
            'ru' => "Только водители могут выполнить это действие.",
            'en' => "Only drivers are allowed to perform this action.",
        ];


        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => $messages[auth()->user()->authLanguage->language ?? 'uz'],
            ], 403)
        );
    }




    public function rules()
    {
        return [

            'vehicle_id' => 'required|exists:vehicles,id',
            'start_quarter_id' => 'required|string|exists:quarters,id',
            'end_quarter_id' => 'required|string|exists:quarters,id',
            'start_region_id' => 'required|string|exists:regions,id',
            'end_region_id' => 'required|string|exists:regions,id',
            'start_district_id' => 'required|string|exists:districts,id',
            'end_district_id' => 'required|string|exists:districts,id',
            'end_time' => ['nullable', 'date', function ($attribute, $value, $fail) {
                if (!request()->has('start_time')) {
                    $fail('Start time is required for validating end time.');
                    return;
                }
                $start_time = Carbon::parse(request()->input('start_time'));
                $end_time = Carbon::parse($value);

                $diffInMinutes = $start_time->diffInMinutes($end_time, false); // false: negative values if end_time < start_time

                $lang = auth()->user()->authLanguage->language ?? 'uz';

                $messages = [
                    'min_diff' => [
                        'uz' => 'Boshlanish va tugash vaqtlarining farqi kamida 10 daqiqa bo‘lishi kerak.',
                        'ru' => 'Разница между временем начала и окончания должна быть не менее 10 минут.',
                        'en' => 'The time difference between start time and end time must be at least 10 minutes.',
                    ],
                    'max_diff' => [
                        'uz' => 'Boshlanish va tugash vaqtlarining farqi 48 soatdan oshmasligi kerak.',
                        'ru' => 'Разница между временем начала и окончания не должна превышать 48 часов.',
                        'en' => 'The time difference between start time and end time must not exceed 48 hours.',
                    ],
                    'end_after_start' => [
                        'uz' => 'Tugash vaqti boshlanish vaqtida keyin bo‘lishi kerak.',
                        'ru' => 'Время окончания должно быть позже времени начала.',
                        'en' => 'End time must be after start time.',
                    ],
                    'start_after_now' => [
                        'uz' => 'Boshlanish vaqti hozirgi vaqtdan oldin bo‘lishi mumkin emas.',
                        'ru' => 'Время начала не может быть раньше текущего времени.',
                        'en' => 'Start time cannot be earlier than the current time.',
                    ],
                ];

                // Boshlanish vaqti hozirgi vaqtdan oldin bo‘lsa
                $now = now();
                if ($start_time < $now) {
                    $fail($messages['start_after_now'][$lang]);
                }

                if ($diffInMinutes < 10) {
                    $fail($messages['min_diff'][$lang]);
                }

                if ($diffInMinutes > 48 * 60) {
                    $fail($messages['max_diff'][$lang]);
                }

                if ($diffInMinutes <= 0) {
                    $fail($messages['end_after_start'][$lang]);
                }
            }],
            'price_per_seat' => 'required|numeric|min:0',
            'available_seats' => 'required|integer',
            'start_lat' => 'nullable|numeric|between:-90,90',
            'start_long' => 'nullable|numeric|between:-180,180',
            'end_lat' => 'nullable|numeric|between:-90,90',
            'end_long' => 'nullable|numeric|between:-180,180',
            'start_time' => ['required', 'date', function ($attribute, $value, $fail) {
                $start_time = Carbon::parse($value);
                $now = Carbon::now();
                $limit = $now->copy()->addHours(48);
                $messages = [
                    'start_time_range' => [
                        'uz' => 'Boshlanish vaqti keyingi 48 soat ichida bo‘lishi kerak.',
                        'ru' => 'Время начала должно быть в пределах следующих 48 часов.',
                        'en' => 'Start time must be within the next 48 hours.',
                    ],
                ];

                if ($start_time->lessThan($now) || $start_time->greaterThan($limit)) {
                    $fail($messages['start_time_range'][auth()->user()->authLanguage->language ?? 'uz']);
                }
            }],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422)
        );
    }
}
