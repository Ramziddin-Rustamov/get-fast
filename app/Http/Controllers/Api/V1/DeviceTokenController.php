<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceTokenController extends Controller
{
    /**
     * Flutter app login/ochilganda FCM device tokenini saqlaydi/yangilaydi.
     *
     * POST /api/v1/device-token
     * body: { "device_token": "...", "device_platform": "android|ios" }
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_token'    => ['required', 'string', 'max:512'],
            'device_platform' => ['nullable', 'string', 'in:android,ios'],
        ]);

        $user = Auth::user();
        $user->device_token    = $data['device_token'];
        $user->device_platform = $data['device_platform'] ?? $user->device_platform;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Device token saqlandi',
        ]);
    }

    /**
     * Logout/o'chirishda tokenni tozalaydi (bu qurilmaga push kelmasin).
     *
     * DELETE /api/v1/device-token
     */
    public function destroy(): JsonResponse
    {
        $user = Auth::user();
        $user->device_token = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Device token o\'chirildi',
        ]);
    }
}
