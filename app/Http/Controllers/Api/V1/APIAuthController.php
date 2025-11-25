<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\V1\SmsService;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\V1\UserImage;
use App\Models\V1\Vehicle;
use App\Models\V1\VehicleImages;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\V1\UserLanguage;

class APIAuthController extends Controller
{

    protected SmsService $smsService;
    public $language;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
        $this->language = $user->authLanguage->language ?? 'en';
    }


    public function register(Request $request)
    {

        try {
            DB::beginTransaction();
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
            // SMS uchun xabar
            $text = "Ro'yhatdan o'tish uchun tasdiqlash kodi: $code";

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

            UserLanguage::updateOrCreate([
                'user_id' => $user->id,
                'language' => 'uz'
            ]);
            DB::commit();

            // smsni navbatga yuborish
            $this->smsService->sendQueued($user->phone, $text, 'register');

            $messages = [
                'uz' => 'Tasdiqlash kodi telefoningizga yuborildi',
                'ru' => 'Код подтверждения отправлен на ваш телефон',
                'en' => 'Verification code sent to your phone',
            ];

            // Agar til mavjud bo‘lmasa, "en" ga tushadi
            $message = $messages[$this->language];

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'user_phone' => $user->phone,
                'code' => $code
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function verifyCode(Request $request)
    {
        try {
            DB::beginTransaction();
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

                UserLanguage::updateOrCreate([
                    'user_id' => $user->id,
                    'language' => 'uz'
                ]);

                DB::commit();
                $messages = [
                    'uz' => "Telefon raqami tasdiqlandi. Foydalanuvchi ro'yxatdan o'tdi.",
                    'ru' => 'Номер телефона подтверждён. Пользователь зарегистрирован.',
                    'en' => 'Phone number verified. User registered.',
                ];

                $message = $messages[$this->language];

                return response()->json([
                    'status' => 'success',
                    'message' => $message,
                    'go' => 'login page',
                ]);
            } else {

                $messages = [
                    'uz' => 'Tasdiqlash kodi noto‘g‘ri.',
                    'ru' => 'Неверный код подтверждения.',
                    'en' => 'Invalid verification code.',
                ];

                $message = $messages[$this->language];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 400);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong' . $e
            ], 500);
        }
    }


    public function resendCode(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'phone' => 'required|string',
            ]);

            $user = \App\Models\User::where('phone', $request->phone)->first();

            if (!$user) {
                $messages = [
                    'uz' => 'Foydalanuvchi topilmadi.',
                    'ru' => 'Пользователь не найден.',
                    'en' => 'User not found.',
                ];

                $message = $messages[$this->language];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 404);
            }

            if ($user->is_verified) {
                $messages = [
                    'uz' => 'Telefon raqami allaqachon tasdiqlangan.',
                    'ru' => 'Номер телефона уже подтверждён.',
                    'en' => 'Phone number already verified.',
                ];

                $message = $messages[$this->language];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 400);
            }

            // Yangi tasdiqlash kodi generatsiyasi
            $code = rand(100000, 999999);
            // SMS uchun xabar
            $text = "Ro'yhatdan o'tish uchun tasdiqlash kodi: $code";

            $user->verification_code = $code;
            $user->save();

            DB::commit();

            // smsni navbatga yuborish
            $this->smsService->sendQueued($user->phone, $text, 'register');
            $messages = [
                'uz' => "Telefoningizga yangi tasdiqlash kodi yuborildi.",
                'ru' => "Новый код подтверждения отправлен на ваш телефон.",
                'en' => "New verification code sent to your phone.",
            ];

            $message = $messages[$this->language];

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'code' => $code
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong' . $e
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'phone' => 'required|string|exists:users,phone',
                'password' => 'required|string',
            ]);

            $credentials = $request->only('phone', 'password');

            if (!$token = Auth::guard('api')->attempt($credentials)) {
                $messages = [
                    'uz' => 'Telefon raqami yoki parol noto‘g‘ri.',
                    'ru' => 'Неверный номер телефона или пароль.',
                    'en' => 'Invalid phone or password.',
                ];

                $message = $messages[$this->language];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 401);
            }

            $user = Auth::guard('api')->user();

            if (!$user->is_verified) {
                $messages = [
                    'uz' => 'Avvalo telefon raqamingizni tasdiqlang.',
                    'ru' => 'Сначала подтвердите свой номер телефона.',
                    'en' => 'Please verify your phone number first.',
                ];

                $message = $messages[$this->language];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 403);
            }

            if ($user->driving_verification_status == 'blocked') {
                $messages = [
                    'uz' => 'Siz havolani qabul qilishdan oldin bloklangansiz. Xohlasangiz bizga murojaat qiling.',
                    'ru' => 'Вы были заблокированы до того, как приняли ссылку. Если хотите, свяжитесь с нами.',
                    'en' => 'You have been blocked before accepting the link. If you want, contact us.',
                ];

                $message = $messages[$this->language];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ]);
            }

            DB::commit();

            $messages = [
                'uz' => 'Tizimga kirish muvaffaqiyatli amalga oshirildi.',
                'ru' => 'Вход выполнен успешно.',
                'en' => 'Login successful.',
            ];

            $message = $messages[$this->language];

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong' . $e
            ], 500);
        }
    }
    public function sendResetCode(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'phone' => 'required|string|exists:users,phone',
            ]);

            $user = \App\Models\User::where('phone', $request->phone)->first();

            // 6 xonali random kod
            $code = rand(100000, 999999);
            $user->verification_code = $code;
            $user->save();
            $messages = [
                'uz' => "Parolni tiklash uchun tasdiqlash kodi: $code",
                'ru' => "Код подтверждения для сброса пароля: $code",
                'en' => "Your password reset code is: $code",
            ];

            $text = $messages[$this->language];

            // SMS yuborish (Queue yoki to‘g‘ridan-to‘g‘ri)
            $this->smsService->sendQueued($user->phone, $text, 'password_reset');
            // SMS yuborish joyi (integratsiya qilasiz)

            DB::commit();
            $messages = [
                'uz' => "Parolni tiklash kodi SMS orqali yuborildi.",
                'ru' => "Код для сброса пароля отправлен через SMS.",
                'en' => "Reset code sent via SMS.",
            ];

            $message = $messages[$this->language];

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'code' => $code
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong' . $e
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'phone' => 'required|string|exists:users,phone',
                'verification_code' => 'required|string',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = \App\Models\User::where('phone', $request->phone)->first();

            if ($user->verification_code !== $request->verification_code) {
                $messages = [
                    'uz' => 'Tasdiqlash kodi noto‘g‘ri.',
                    'ru' => 'Неверный код подтверждения.',
                    'en' => 'Invalid verification code.',
                ];

                $message = $messages[$this->language];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 400);
            }

            // Parolni yangilash
            $user->password = bcrypt($request->password);
            $user->verification_code = null; // Kodni tozalash
            $user->save();

            DB::commit();
            $messages = [
                'uz' => "Parol muvaffaqiyatli tiklandi.",
                'ru' => "Пароль успешно сброшен.",
                'en' => "Password has been reset successfully.",
            ];

            $message = $messages[$this->language];

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'return' => 'login page'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong' . $e
            ], 500);
        }
    }


    public function logout()
    {
        Auth::logout();

        $messages = [
            'uz' => "Muvaffaqiyatli tizimdan chiqildi.",
            'ru' => "Вы успешно вышли из системы.",
            'en' => "Successfully logged out.",
        ];

        $message = $messages[$this->language];

        return response()->json([
            'status' => 'success',
            'message' => $message,
        ]);
    }

    public function refresh()
    {
        $messages = [
            'uz' => "Token muvaffaqiyatli yangilandi.",
            'ru' => "Токен успешно обновлён.",
            'en' => "Token refreshed successfully.",
        ];

        $message = $messages[$this->language];

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }


    public function becomeDriver(Request $request)
    {


        try {

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
            DB::beginTransaction();

            $user = Auth::user();
            $user->driving_licence_number = $request->driving_license_number;
            $user->driving_licence_expiry = $request->driving_license_expiration_date;
            $user->birth_date = $request->birthday;
            $user->role = 'driver';
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
            $messages = [
                'uz' => "Mashina muvaffaqiyatli yaratildi, keyingi bosqichga o‘ting.",
                'ru' => "Автомобиль успешно создан, переходите к следующему шагу.",
                'en' => "Vehicle created successfully, jump to the next step.",
            ];

            $message = $messages[$this->language];

            return response()->json([
                'status' => 'success',
                'message' => $message,
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
        DB::beginTransaction();

        try {

            $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'tech_passport_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'tech_passport_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'car_images' => 'nullable|array|min:1',
                'car_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            ]);
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

            DB::commit();

            $messages = [
                'uz' => "Mashina rasmlari muvaffaqiyatli yuklandi.",
                'ru' => "Изображения автомобиля успешно загружены.",
                'en' => "Vehicle images uploaded successfully.",
            ];

            $message = $messages[$this->language];

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            $messages = [
                'uz' => "Mashina rasmlarini yuklashda xatolik yuz berdi. Xato: ",
                'ru' => "Не удалось загрузить изображения автомобиля. Ошибка: ",
                'en' => "Failed to upload vehicle images. Error: ",
            ];

            $message = $messages[$this->language] . $e->getMessage();

            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 500);
        }
    }




    public function uploadDriverDocuments(Request $request)
    {


        try {
            DB::beginTransaction();
            $request->validate([
                'driving_licence_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'driving_licence_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'driver_passport_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            ]);


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

            DB::commit();

            $messages = [
                'uz' => "Haydovchi hujjatlari yuklandi. Iltimos, admin tasdiqlashini kuting.",
                'ru' => "Документы водителя загружены. Пожалуйста, дождитесь одобрения администратора.",
                'en' => "Driver documents uploaded. Please wait for admin approval.",
            ];

            $message = $messages[$this->language];

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();


            $messages = [
                'uz' => "Haydovchi hujjatlarini yuklashda xatolik yuz berdi. Xato: ",
                'ru' => "Не удалось загрузить документы водителя. Ошибка: ",
                'en' => "Failed to upload driver documents. Error: ",
            ];

            $message = $messages[$this->language] . $e->getMessage();

            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 500);
        }
    }




    public function updateProfile(Request $request)
    {


        try {
            DB::beginTransaction();
            $request->validate([
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'father_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // rasm
            ]);


            // Foydalanuvchini topish
            $user = User::find(Auth::user()->id);

            if (!$user) {
                $messages = [
                    'uz' => 'Foydalanuvchi topilmadi.',
                    'ru' => 'Пользователь не найден.',
                    'en' => 'User not found.',
                ];

                $message = $messages[$this->language];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
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
            $messages = [
                'uz' => "Foydalanuvchi muvaffaqiyatli yangilandi.",
                'ru' => "Пользователь успешно обновлён.",
                'en' => "User updated successfully.",
            ];

            $message = $messages[$this->language];

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $messages = [
                'uz' => "Xatolik yuz berdi! Xato: ",
                'ru' => "Произошла ошибка! Ошибка: ",
                'en' => "An error occurred! Error: ",
            ];

            $message = $messages[$this->language] . $e->getMessage();

            return response()->json([
                'message' => $message,
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function me()
    {
        try {
            $user = Auth::user();

            $image = UserImage::where('user_id', $user->id)->where('type', 'profile')->first();
            $passport = UserImage::where('user_id', $user->id)->where('type', 'passport')->first();
            $drivingLicence = UserImage::where('user_id', $user->id)->where('type', 'driving_licence')->first();
            $image = $user->profileImage;
            return response()->json([
                'status' => 'success',
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'father_name' => $user->father_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'birth_date' => $user->birth_date,
                    'driving_verification_status' => $user->driving_verification_status,
                    'created_at' => $user->created_at,
                    'image' => $image ? asset($image->image_path) : null,
                    'balance' => $user->myBalance ? [
                        'balance' => $user->myBalance->balance,
                        'after_tax' => $user->myBalance->after_tax,
                        'tax' => $user->myBalance->tax,
                        'locked_balance' => $user->myBalance->locked_balance,
                        'currency' => $user->myBalance->currency
                    ] : 0,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function updateUserLanguage(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'language' => 'required|in:uz,en,ru',
            ]);

            $lang = UserLanguage::updateOrCreate(
                ['user_id' => auth()->id()],
                ['language' => $request->language]
            );

            DB::commit();

            $messages = [
                'uz' => "Til muvaffaqiyatli yangilandi.",
                'ru' => "Язык успешно обновлён.",
                'en' => "Language updated successfully.",
            ];

            $message = $messages[$this->language];

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'language' => $lang->language,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
