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

class DriverController extends Controller
{
    public function index()
    {
        $drivers = User::where('role', 'driver')
            ->with(['balance', 'vehicles', 'driverTrips', 'myVehicle'])
            ->orderBy('id', 'desc') // Eng oxirgi haydovchilar birinchi bo‘lib chiqadi
            ->paginate(10);

        return view('admin-views.drivers.index', compact('drivers'));
    }


    /**
     * Yangi driver qo'shish formasi
     */
    public function create()
    {
        $regions = Region::all();
        return view('admin-views.drivers.create', compact('regions'));
    }

    /**
     * Yangi driverni saqlash
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'quarter_id' => 'required|exists:quarters,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'role' => 'driver',
            'password' => Hash::make($request->phone),
            'region_id' => $request->region_id,
            'district_id' => $request->district_id,
            'quarter_id' => $request->quarter_id,
        ]);

        $vehicle = new Vehicle();
        $vehicle->user_id = $user->id;
        $vehicle->make = $request->make;
        $vehicle->model = $request->model;
        $vehicle->year = $request->year;
        $vehicle->license_plate = $request->license_plate;
        $vehicle->seats = $request->seats;
        $vehicle->save();
        return redirect()->route('admin-views.drivers.index')->with('success', 'Driver added successfully!');
    }

    /**
     * Bitta driver ma'lumotlarini ko'rsatish
     */
    public function show($driver)
    {
        $driver = User::where('role', 'driver')->with(['balance', 'vehicles', 'driverTrips', 'myVehicle'])->find($driver);
        return view('admin-views.drivers.show', compact('driver'));
    }

    /**
     * Driverni tahrirlash formasi
     */
    public function edit($driver)
    {
        $driver = User::where('role', 'driver')->find($driver);

        return view('admin-views.drivers.edit', compact('driver'));
    }

    /**
     * Driverni yangilash
     */
    public function update(Request $request, User $driver)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:drivers,phone,' . $driver->id,
        ]);

        $driver->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->phone),
        ]);

        // $driver ning ID sini olish
        $driverId = $driver->id;

        $vehicle = Vehicle::where('user_id', $driverId)->first();

        if ($vehicle) {
            $vehicle->make = $request->make;
            $vehicle->model = $request->model;
            $vehicle->year = $request->year;
            $vehicle->license_plate = $request->license_plate;
            $vehicle->seats = $request->seats;
            $vehicle->save();
        } else {
            // Agar haydovchining mashinasi bo‘lmasa, yangi yozuv yaratish
            Vehicle::create([
                'user_id' => $driverId,
                'make' => $request->make,
                'model' => $request->model,
                'year' => $request->year,
                'license_plate' => $request->license_plate,
                'seats' => $request->seats,
            ]);
        }



        return redirect()->route('drivers.index')->with('success', 'Driver updated successfully!');
    }

    /**
     * Driverni o'chirish
     */
    public function destroy(User $driver)
    {
        $driver->delete();
        return redirect()->route('drivers.index')->with('success', 'Driver deleted successfully!');
    }
    public function resetBalance($driverId)
    {
        // Haydovchining joriy balansi
        $currentBalance = Balance::where('user_id', $driverId)->sum('balance');

        if ($currentBalance > 0) {
            // To'lov tarixini saqlash
            DriverPayment::create([
                'admin_id' => Auth::id(),  // Kim pulni nolladi (Admin)
                'driver_id' => $driverId,  // Haydovchi ID
                'amount' => $currentBalance, // Qancha summa nollandi
                'transaction_date' => now(),
            ]);

            // Balansni 0 ga tushirish
            Balance::where('user_id', $driverId)->update(['balance' => 0]);
        }

        return back()->with('success', 'Balans muvaffaqiyatli 0 ga tushirildi!');
    }
}
