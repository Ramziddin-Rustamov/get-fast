<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawRequestController extends Controller
{
    // 1. Request yaratish
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'amount' => 'required|numeric|min:10000',
                'card_id' => 'required',
                'card_holder' => 'nullable|string'
            ]);

            $user = Auth::user();
            $lang = auth()->user()->authLanguage->language ?? 'uz';

            // balans tekshirish
            if ($user->balance->balance < $request->amount) {
                $message = [
                    'uz' => 'Balans yetarli emas',
                    'ru' => 'Недостаточно средств',
                    'en' => 'Insufficient funds',
                ];
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => $message[$lang],
                ], 400);
            }

            // pending request borligini tekshirish
            $exists = WithdrawRequest::where('user_id', $user->id)
                ->where('status', 'pending')
                ->exists();

            if ($exists) {
                $message = [
                    'uz' => 'Sizda allaqachon pending request mavjud',
                    'ru' => 'У вас уже есть запрос в ожидании',
                    'en' => 'You have a pending request',
                ];
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => $message[$lang],
                ], 400);
            }

            $withdraw = WithdrawRequest::create([
                'user_id' => $user->id,
                'role' => $user->role, // client yoki driver
                'amount' => $request->amount,
                'card_id' => $request->card_id,
                'card_holder' => $request->card_holder,
            ]);

            $successMessage = [
                'uz' => 'Request muvaffaqiyatli yaratildi',
                'ru' => 'Запрос успешно создан',
                'en' => 'Request created successfully',
            ];
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => $successMessage[$lang] ?? $successMessage['uz'],
                'data' => $withdraw
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Request creation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // 2. User o‘z requestlarini ko‘rish
    public function index()
    {
        $user = Auth::user();
        $lang = auth()->user()->authLanguage->language ?? 'uz';
        $response =  WithdrawRequest::where('user_id', $user->id)->get();
        if ($response) {
            $message = [
                'uz' => ' Hamma pul uchun surovlar topildi',
                'ru' => 'Все запросы на снятие денег получены',
                'en' => 'All withdrawal requests received'
            ];
            return response()->json([
                'status' => 'success',
                'message' => $message[$lang] ?? $message['uz'],
                'data' => $response
            ], 200);
        }
        $message = [
            'uz' => 'Requestlar topilmadi',
            'ru' => 'Запросы не найдены',
            'en' => 'Requests not found',
        ];
        return response()->json([
            'status' => 'error',
            'message' => $message[$lang] ?? $message['uz'],
            'data' => []
        ]);
    }

   
}
