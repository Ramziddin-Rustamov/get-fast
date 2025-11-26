<?php

namespace App\Http\Controllers\Admin;


use App\Models\User;
use App\Models\V1\Balance;
use App\Models\V1\DriverPayment;
use App\Models\V1\Region;
use App\Models\V1\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\BalanceTransaction;
use App\Models\UserBalance;
use App\Models\V1\Card;
use App\Models\V1\VehicleImages;
use App\Services\V1\SmsService;

class DriverController extends Controller
{

    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }


    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status'); // none, pending, approved, rejected, blocked

        $drivers = User::where('role', 'driver')
            ->with(['balance', 'vehicles', 'driverTrips', 'myVehicle'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query, $status) {
                if ($status === 'none') {
                    $query->where('driving_verification_status', 'none');
                } elseif ($status === 'pending') {
                    $query->where('driving_verification_status', 'pending');
                } elseif ($status === 'approved') {
                    $query->where('driving_verification_status', 'approved');
                } elseif ($status === 'rejected') {
                    $query->where('driving_verification_status', 'rejected');
                } elseif ($status === 'blocked') {
                    $query->where('driving_verification_status', 'blocked');
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin-views.drivers.index', compact('drivers', 'status', 'search'));
    }

    /**
     * Bitta driver ma'lumotlarini ko'rsatish
     */
    public function show($driver)
    {

        $driver = User::where('role', 'driver')->with(['balance', 'vehicles', 'driverTrips', 'myVehicle', 'cards'])->find($driver);
        if (!$driver) {
            return redirect()->route('drivers.index')->with('error', 'Haydavchi topilmadi ');
        }

        $vehicles = Vehicle::where('user_id', $driver->id)->get();
        if (empty($vehicles)) {
            return redirect()->view('admin-views.drivers.index')->with('error', 'Moshina topilmadi hozircha !');
        }
        $driverImages = $driver->images; // user_images
        $vehicleImages = VehicleImages::whereIn('vehicle_id', $vehicles->pluck('id'))->get();



        // Oxirgi tranzaksiyalar paginate
        $balanceTransactions = $driver->balanceTransactions()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $vehicles = $driver->vehicles()->orderBy('id', 'desc')->paginate(3);
        $trips = $driver->driverTrips()->orderBy('id', 'desc')->paginate(3);
        return view('admin-views.drivers.show', compact(
            'driver',
            'balanceTransactions',
            'vehicles',
            'driverImages',
            'vehicleImages',
            'balanceTransactions'
        ));
    }

    public function destroy(User $driver)
    {
        $driver->delete();
        return redirect()->route('drivers.index')->with('success', 'Driver deleted successfully!');
    }



    public function sendSms(Request $request, $driverId)
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $driver = User::where('role', 'driver')->find($driverId);
        $phone = $driver->phone;
        $message = $request->input('message');
        $this->smsService->sendQueued($phone, $message, 'message-to-driver');


        return redirect()->back()->with('success', 'Xabar muvaffaqiyatli yuborildi ' . $phone . ': ' . $message);
    }

    public function transferBalance(Request $request, $driverId)
    {


        try {

            $driver = User::where('role', 'driver')->find($driverId);
            $driverBalance = UserBalance::where('user_id', $driverId)->first();

            $balanceTransaction = BalanceTransaction::create([
                'user_id' => $driverId,
                'type' => 'debit',
                'amount' => $request->amount,
                'balance_before' => $driverBalance->balance,
                'balance_after' => $driverBalance->balance - $request->amount,
                'trip_id' => null,
                'status' => 'success',
                'reason' => ' Haydavchining ishlagan ' . $request->amount . ' so‘m puli muvaffaqiyatli transfer qilindi.' . $request->card_number . ' raqamiga',
                'reference_id' => null,
            ]);

            $driverBalance->balance = $driverBalance->balance - $request->amount;
            $driverBalance->save();
            // $message = ' Haydavchining ishlagan ' . $request->amount . ' so‘m puli muvaffaqiyatli transfer qilindi.' . $request->card_number . ' raqamiga';
            // $this->smsService->sendQueued($driver->phone, $message, 'message-to-driver');


            return back()->with('success', 'Haydavchining ishlagan ' . $request->amount . ' so‘m puli muvaffaqiyatli transfer qilindi.' . $request->card_number . ' raqamiga');
        } catch (\Exception $e) {
            return back()->with(
                [
                    'status' => 'error',
                    'message' => 'Xatolik yuz berdi: ' . $e->getMessage()
                ]
            );
        }
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:none,pending,approved,rejected,blocked',
        ]);

        $driver = User::where('role', 'driver')->where('id', $id)->first();
        $driver->driving_verification_status = $request->status;
        $driver->save();


        $message = [
            'uz' => 'Sizning haydovchi statusingiz muvaffaqiyatli yangilandi!' . $request->status,
            'ru' => 'Ваш статус водителя успешно обновлен! ' . $request->status,
            'en' => 'Your driver status has been successfully updated!' . $request->status,
        ];

        // sms logic here
        // $this->smsService->sendQueued($driver->phone, $message[$driver->authLanguage->language], 'message-to-driver-about-driver-status');


        return redirect()->back()->with('success', 'Driver status muvaffaqiyatli yangilandi!, va bu haqida foydalanuvchiga xabar yuborildi.');
    }




    public function deleteAllDriverImages($driverId)
    {
        $driver = User::with('images')->findOrFail($driverId);

        foreach ($driver->images as $img) {

            // Faylni storage dan o‘chirish
            $filePath = storage_path('app/public/' . $img->image_path);

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // DB dan o‘chiramiz
            $img->delete();
        }

        return back()->with('success', 'Hamma haydovchi rasmlari o‘chirildi');
    }

    public function deleteAllVehicleImages($vehicleId)
    {
        $vehicle = Vehicle::with('images')->findOrFail($vehicleId);

        foreach ($vehicle->images as $img) {

            $filePath = storage_path('app/public/' . $img->image_path);

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $img->delete();
        }

        return back()->with('success', 'Barcha moshina rasmlari o‘chirildi');
    }
}
