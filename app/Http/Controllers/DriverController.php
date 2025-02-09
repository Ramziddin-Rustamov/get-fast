<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    /**
     * Driverlar ro'yxatini chiqarish
     */
    public function index()
    {
        $drivers = User::where('role', 'driver')->get();
        return view('drivers.index', compact('drivers'));
    }

    /**
     * Yangi driver qo'shish formasi
     */
    public function create()
    {
        return view('drivers.create');
    }

    /**
     * Yangi driverni saqlash
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:drivers',
        ]);

        User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'role' => 'driver',
        ]);

        return redirect()->route('drivers.index')->with('success', 'Driver added successfully!');
    }

    /**
     * Bitta driver ma'lumotlarini ko'rsatish
     */
    public function show(User $driver)
    {
        return view('drivers.show', compact('driver'));
    }

    /**
     * Driverni tahrirlash formasi
     */
    public function edit(User $driver)
    {
        return view('drivers.edit', compact('driver'));
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
        ]);

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
}
