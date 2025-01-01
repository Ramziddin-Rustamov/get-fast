<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class ReviewUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trip_id' => 'sometimes|exists:trips,id',
            'reviewer_id' => 'sometimes|nullable|exists:users,id',
            'reviewed_user_id' => 'sometimes|nullable|exists:users,id',
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }
}
