<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
class APIAuthController extends Controller
{




    public function register(Request $request)
    {
        // Step 1: Validatsiya
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6|confirmed', // confirmation uchun `password_confirmation` kerak
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Step 2: Tasdiqlash kodi generatsiya qilish
        $code = rand(100000, 999999); // 6 xonali kod

        // Step 3: Foydalanuvchini vaqtincha yaratish (is_verified = false)
        $user = \App\Models\User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'verification_code' => $code,
            'is_verified' => false,
        ]);

        // Step 4: Kodni foydalanuvchiga yuborish (SMS orqali)
        // Bu joyga SMS yuborish funksiyasini joylashtiring
        // Misol: SmsService::send($user->phone, "Your verification code is: $code");

        return response()->json([
            'status' => 'success',
            'message' => 'Verification code sent to your phone',
            'user_id' => $user->id,
            'code' => $code
        ]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string'
        ]);

        $user = \App\Models\User::find($request->user_id);

        if ($user->verification_code === $request->code) {
            $user->is_verified = true;
            $user->verification_code = null;
            $user->save();


            return response()->json([
                'status' => 'success',
                'message' => 'Phone number verified. User registered.',
                'go' => 'login page',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification code',
            ], 400);
        }
    }


    public function resendCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $user = \App\Models\User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        if ($user->is_verified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Phone number already verified'
            ], 400);
        }

        // Yangi tasdiqlash kodi generatsiyasi
        $code = rand(100000, 999999);
        $user->verification_code = $code;
        $user->save();

        // SMS orqali yuborish logikasi (shu joyga integratsiya qo‘shasiz)
        // Misol: SmsService::send($user->phone, "Your new verification code is: $code");

        return response()->json([
            'status' => 'success',
            'message' => 'New verification code sent to your phone',
            'code' => $code
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);
    
        $credentials = $request->only('phone', 'password');
    
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid phone or password',
            ], 401);
        }
    
        $user = Auth::guard('api')->user();
    
        if (!$user->is_verified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please verify your phone number first.',
            ], 403);
        }
    
        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
        ]);

        $user = \App\Models\User::where('phone', $request->phone)->first();

        // 6 xonali random kod
        $code = rand(100000, 999999);
        $user->verification_code = $code;
        $user->save();

        // SMS yuborish joyi (integratsiya qilasiz)
        // SmsService::send($user->phone, "Your password reset code is: $code");

        return response()->json([
            'status' => 'success',
            'message' => 'Reset code sent via SMS',
            'code' => $code
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'verification_code' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = \App\Models\User::where('phone', $request->phone)->first();

        if ($user->verification_code !== $request->verification_code) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification code',
            ], 400);
        }

        // Parolni yangilash
        $user->password = bcrypt($request->password);
        $user->verification_code = null; // Kodni tozalash
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password has been reset successfully',
            'return' => 'login page'
        ]);
    }








    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
