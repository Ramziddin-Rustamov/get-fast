<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\BalanceTransaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\V1\UserImage;
use App\Models\V1\Vehicle;
use App\Models\V1\VehicleImages;
use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;
use Laravel\Ui\Presets\React;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class APIAuthController extends Controller
{




    public function register(Request $request)
    {
        // Step 1: Validatsiya
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:users,phone',
            'email' => 'required|string|unique:users,email',
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
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'father_name' => $request->father_name,
            'email' => $request->email,
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
            'user_phone' => $user->phone,
            'code' => $code
        ]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|exists:users,phone',
            'code' => 'required|string'
        ]);

        $user = \App\Models\User::where('phone', $request->phone)->first();

        if ($user->verification_code === $request->code) {
            $user->is_verified = true;
            $user->verification_code = null;
            $user->save();

            $userbalalce = UserBalance::create([
                'user_id' => $user->id,
                'balance' => 0.00,
            ]);


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
            'phone' => 'required|string|exists:users,phone',
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


    public function becomeDriver(Request $request)
    {
        $request->validate([
            'driving_license_number' => 'required|string',
            'driving_license_expiration_date' => 'required|string',
            'birthday' => 'required|string',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'quarter_id' => 'required|exists:quarters,id',
            'home_address' => 'required|string',
            'vehicle_number' => 'required|string|unique:vehicles,car_number',
            'car_model' => 'required|string',
            'car_color_id' => 'required|exists:colors,id',
            'seats' => 'required|integer|min:1|max:8',
            'tech_passport_number' => 'required|string|unique:vehicles,tech_passport_number',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $user->driving_licence_number = $request->driving_license_number;
            $user->driving_licence_expiry = $request->driving_license_expiration_date;
            $user->birth_date = $request->birthday;
            $user->driving_verification_status = 'pending';
            $user->region_id = $request->region_id;
            $user->district_id = $request->district_id;
            $user->quarter_id = $request->quarter_id;
            $user->home = $request->home_address;
            $user->save();

            $vehicle = Vehicle::create([
                'user_id' => $user->id,
                'color_id' => $request->car_color_id,
                'model' => $request->car_model,
                'car_number' => $request->vehicle_number,
                'tech_passport_number' => $request->tech_passport_number,
                'seats' => $request->seats,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle created successfully, jump to the next step',
                'vehicle_id' => $vehicle->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create vehicle.' . $e
            ], 500);
        }
    }



    public function uploadVehicleImages(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'tech_passport_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'tech_passport_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'car_images' => 'nullable|array|min:1',
            'car_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        try {
            $vehicleId = $request->vehicle_id;

            // ✅ CAR IMAGES: avval eski rasm va fayllarni o‘chirish
            if ($request->hasFile('car_images')) {
                $existingImages = VehicleImages::where('vehicle_id', $vehicleId)
                    ->where('type', 'vehicle')
                    ->get();

                foreach ($existingImages as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }

                foreach ($request->file('car_images') as $image) {
                    $path = $image->store("vehicles/cars/{$vehicleId}", 'public');
                    VehicleImages::create([
                        'vehicle_id' => $vehicleId,
                        'image_path' => $path,
                        'type' => 'vehicle',
                    ]);
                }
            }

            // ✅ TECH PASSPORT FRONT
            if ($request->hasFile('tech_passport_front')) {
                $existingFront = VehicleImages::where('vehicle_id', $vehicleId)
                    ->where('type', 'tech_passport')
                    ->where('side', 'front')
                    ->first();

                if ($existingFront) {
                    Storage::disk('public')->delete($existingFront->image_path);
                    $existingFront->delete();
                }

                $path = $request->file('tech_passport_front')->store("vehicles/tech_passports/front/vehicle/{$vehicleId}/", 'public');
                VehicleImages::create([
                    'vehicle_id' => $vehicleId,
                    'image_path' => $path,
                    'type' => 'tech_passport',
                    'side' => 'front',
                ]);
            }

            // ✅ TECH PASSPORT BACK
            if ($request->hasFile('tech_passport_back')) {
                $existingBack = VehicleImages::where('vehicle_id', $vehicleId)
                    ->where('type', 'tech_passport')
                    ->where('side', 'back')
                    ->first();

                if ($existingBack) {
                    Storage::disk('public')->delete($existingBack->image_path);
                    $existingBack->delete();
                }

                $path = $request->file('tech_passport_back')->store("vehicles/tech_passports/back/vehicle/{$vehicleId}", 'public');
                VehicleImages::create([
                    'vehicle_id' => $vehicleId,
                    'image_path' => $path,
                    'type' => 'tech_passport',
                    'side' => 'back',
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle images uploaded successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload vehicle images. Error: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function uploadDriverDocuments(Request $request)
    {
        $request->validate([
            'driving_licence_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'driving_licence_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'driver_passport_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        try {
            $user = Auth::user();
            $userId = $user->id;

            // Helper function to update one image
            $updateImage = function ($file, $folder, $type, $side = null) use ($userId) {
                // Eski rasmni topish
                $query = UserImage::where('user_id', $userId)->where('type', $type);
                if ($side) {
                    $query->where('side', $side);
                }

                $oldImage = $query->first();

                // Eski faylni o‘chirish
                if ($oldImage && Storage::disk('public')->exists($oldImage->image_path)) {
                    Storage::disk('public')->delete($oldImage->image_path);
                    $oldImage->delete(); // bazadan ham o‘chir
                }

                // Yangi faylni saqlash
                $path = $file->store($folder, 'public');

                // Bazaga yozish
                UserImage::create([
                    'user_id' => $userId,
                    'image_path' => $path,
                    'type' => $type,
                    'side' => $side,
                ]);
            };

            // Driving Licence Front
            if ($request->hasFile('driving_licence_front')) {
                $updateImage(
                    $request->file('driving_licence_front'),
                    "drivers/driving_licences/{$userId}",
                    'driving_licence',
                    'front'
                );
            }

            // Driving Licence Back
            if ($request->hasFile('driving_licence_back')) {
                $updateImage(
                    $request->file('driving_licence_back'),
                    "drivers/driving_licences/{$userId}",
                    'driving_licence',
                    'back'
                );
            }

            // Passport
            if ($request->hasFile('driver_passport_image')) {
                $updateImage(
                    $request->file('driver_passport_image'),
                    "drivers/passports/{$userId}",
                    'passport'
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Driver documents uploaded. Please wait for admin approval.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload driver documents.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    public function updateProfile(Request $request)
    {

        $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'father_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // rasm
        ]);

        DB::beginTransaction();
        try {
            // Foydalanuvchini topish
            $user = User::find(Auth::user()->id);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404);
            }

            // Ma'lumotlarni yangilash
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->father_name = $request->father_name;
            $user->email = $request->email;
            $user->save();

            $this->handleImageUpdate($request, $user->id, 'image', 'profile', 'uploads/profile');
            $this->handleImageUpdate($request, $user->id, 'passport', 'passport', 'uploads/passport');
            $this->handleImageUpdate($request, $user->id, 'driving_licence', 'driving_licence', 'uploads/driving');


            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Foydalanuvchi muvaffaqiyatli yangilandi.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Xatolik yuz berdi!',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function me()
    {
        $user = Auth::user();

        $image = UserImage::where('user_id', $user->id)->where('type', 'profile')->first();
        $passport = UserImage::where('user_id', $user->id)->where('type', 'passport')->first();
        $drivingLicence = UserImage::where('user_id', $user->id)->where('type', 'driving_licence')->first();

        return response()->json([
            'status' => 'success',
            'user' => $user,

            'user_image' => $image ? [
                'type' => $image->type,
                'user_image' => asset($image->image_path),
                'user_id' => $user->id,
            ] : null,

            'passport' => $passport ? [
                'type' => $passport->type,
                'user_image' => asset($passport->image_path),
            ] : null,

            'driving_licence' => $drivingLicence ? [
                'type' => $drivingLicence->type,
                'user_image' => asset($drivingLicence->image_path),
            ] : null,

            'my_balance' => $user->myBalance ? [
                'balance' => $user->myBalance->balance,
                'locked_balance' => $user->myBalance->locked_balance,
                'currency' => $user->myBalance->currency,
                'created_at' => $user->myBalance->created_at,
                'updated_at' => $user->myBalance->updated_at,
            ] : null,
        ]);
    }


    public function fillBalance(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
        ]);

        if ($request->amount <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Amount must be greater than 0',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $currentBalance = $user->myBalance?->balance ?? 0;

            // 1. Tranzaksiya yozuvi
            $balanceTransaction = BalanceTransaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $request->amount,
                'balance_before' => $currentBalance,
                'balance_after' => $currentBalance + $request->amount,
                'trip_id' => null,
                'status' => 'success',
                'reason' => 'Balance filled manually by user',
                'reference_id' => null,
            ]);

            // 2. Balansni yangilash yoki yaratish
            $user->myBalance()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'balance' => $currentBalance + $request->amount,
                    'currency' => 'UZS',
                    'tax' => '0.14',
                    'after_taxes' => $currentBalance + $request->amount
                ]
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Balance filled successfully',
                'transaction' => [
                    'id' => $balanceTransaction->id,
                    'type' => $balanceTransaction->type,
                    'amount' => number_format($balanceTransaction->amount, 2),
                    'balance_before' => number_format($balanceTransaction->balance_before, 2),
                    'balance_after' => number_format($balanceTransaction->balance_after, 2),

                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while filling balance.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function approveClientAsDriver(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $user->role = 'driver';
        $user->driving_verification_status = 'approved';
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User approved as driver',
        ]);
    }

    public function rejectClientAsDriver(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);


        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $user->role = 'client';
        $user->driving_verification_status = 'rejected';
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User rejected for driving verification',
        ]);
    }
}
