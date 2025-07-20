<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
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
            'user_id' => $user->phone,
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


    public function become_a_driver(Request $request)
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
            'tech_passport_number' => 'required|string|unique:vehicles,tech_passport_number',
            'seats' => 'required|integer|min:1|max:8',
            'car_images' => 'required|array|min:1',
            'car_images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:44024',
            'tech_passport' => 'required|image|mimes:jpeg,png,jpg,gif|max:2448',
            'driving_licence' => 'required|image|mimes:jpeg,png,jpg,gif|max:24048',
            'driver_passport_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2448',
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

            $vehicle = new Vehicle();
            $vehicle->user_id = $user->id;
            $vehicle->color_id = $request->car_color_id;
            $vehicle->model = $request->car_model;
            $vehicle->tech_passport_number = $request->tech_passport_number;
            $vehicle->car_number = $request->vehicle_number;
            $vehicle->seats = $request->seats;
            $vehicle->save();
            // done
            if ($request->hasFile('car_images')) {
                $paths = [];

                foreach ($request->file('car_images') as $image) {
                    // Faylni saqlash va haqiqiy yo‘lini olish
                    $path = $image->store('vehicles/cars/' . $user->id, 'public');
                    $paths[] = $path;
                }

                // Saqlash
                $vehicleImage = new VehicleImages();
                $vehicleImage->vehicle_id = $vehicle->id;
                $vehicleImage->image_path = json_encode($paths); // array of image paths
                $vehicleImage->type = 'vehicle';
                $vehicleImage->save(); // <-- MUHIM
            }


            if ($request->hasFile('driving_licence')) {
                $filename = time() . '.' . $request->file('driving_licence')->getClientOriginalExtension();
                $path = 'drivers/driving_licences/' . $user->id;
                $storedPath = $request->file('driving_licence')->storeAs($path, $filename, 'public');

                $userImage = new UserImage();
                $userImage->user_id = $user->id;
                $userImage->image_path = $storedPath;
                $userImage->type = 'driving_licence';
                $userImage->save();
            }

            if ($request->hasFile('driver_passport_image')) {
                $filename = time() . '.' . $request->file('driver_passport_image')->getClientOriginalExtension();
                $path = 'drivers/passports/' . $user->id;
                $path_for_driving_licence = $request->file('driver_passport_image')->storeAs($path, $filename, 'public');

                $userImage = new UserImage();
                $userImage->user_id = $user->id;
                $userImage->image_path = $path_for_driving_licence;
                $userImage->type = 'passport';
                $userImage->save();
            }


            if ($request->hasFile('tech_passport')) {
                $filename = time() . '.' . $request->file('tech_passport')->getClientOriginalExtension();
                $path = 'drivers/tech_passports/' . $user->id;
                $path_for_tech_passport = $request->file('tech_passport')->storeAs($path, $filename, 'public');

                $vehicleImage = new VehicleImages();
                $vehicleImage->vehicle_id = $vehicle->id;
                $vehicleImage->image_path = $path_for_tech_passport;
                $vehicleImage->type = 'tech_passport';
                $vehicleImage->save();
            }


            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Applied for driver status successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            FacadesLog::error('Driver registration failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.' . $e->getMessage(),
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

    private function handleImageUpdate(Request $request, $userId, $inputName, $type, $uploadPath)
    {
        if ($request->hasFile($inputName)) {
            $image = $request->file($inputName);
            $imageName = time() . '_' . uniqid() . '.' . $image->extension();
            $image->move(public_path($uploadPath), $imageName);

            // Eski rasmni topish
            $oldImage = UserImage::where('user_id', $userId)
                ->where('type', $type)
                ->first();

            // Eski rasm faylini o'chirish
            if ($oldImage && file_exists(public_path($oldImage->image_path))) {
                unlink(public_path($oldImage->image_path));
            }

            // Bazani yangilash yoki yaratish
            UserImage::updateOrCreate(
                ['user_id' => $userId, 'type' => $type],
                ['image_path' => $uploadPath . '/' . $imageName]
            );
        }
    }

    public function me()
    {
        $user = Auth::user();
        $image = UserImage::where('user_id', $user->id)->where('type', 'profile')->first();
        $passport = UserImage::where('user_id', $user->id)->where('type', 'passport')->first();
        $path_for_driving_licence = UserImage::where('user_id', $user->id)->where('type', 'driving_licence')->first();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'user_image' => [
                'type' => $image->type ?? null,
                'user_image' => asset($image->image_path) ?? null,
                'user_id' => $user->id
            ] ?? null,
            'passport' => [
                'type' => $passport->type ?? null,
                'user_image' => asset($passport->image_path) ?? null,
            ] ?? null,
            'driving_licence' => [
                'type' => $path_for_driving_licence->type ?? null,
                'user_image' => asset($path_for_driving_licence->image_path) ?? null,
            ] ?? null

        ]);
    }
}
