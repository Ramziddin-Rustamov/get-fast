<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupportMessageController extends Controller
{
    /**
     * Xabar yuborish (Client/Admin/Guest)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:support_messages,email',
            'message' => 'required|string',
        ]);

        $supportMessage = SupportMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'message' => $validated['message'],
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' =>  'Xabaringiz muvaffaqiyatli yuborildi',
            'data' => $supportMessage
        ], 201);
    }
}
