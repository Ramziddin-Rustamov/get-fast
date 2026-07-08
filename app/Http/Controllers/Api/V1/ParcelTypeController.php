<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\ParcelType;

class ParcelTypeController extends Controller
{
    /**
     * Faol pochta turlari ro'yxati (mobil ilovadagi checkboxlar uchun),
     * foydalanuvchi tilida.
     */
    public function index()
    {
        $lang = auth()->user()->authLanguage->language ?? 'uz';

        $types = ParcelType::active()
            ->orderBy('id')
            ->get()
            ->map(fn (ParcelType $type) => [
                'id' => $type->id,
                'name' => $type->{'name_' . $lang} ?? $type->name_uz,
                'icon' => $type->icon,
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pochta turlari muvaffaqiyatli olindi',
            'data' => $types,
        ]);
    }
}
