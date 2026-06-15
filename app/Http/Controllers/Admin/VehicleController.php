<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\V1\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::latest()->paginate(10);

        return view('admin-views.vehicle.index', compact('vehicles'));
    }


    public function create()
    {
        $drivers = User::where('role', 'driver')
            ->orderBy('district_id', 'ASC')
            ->get();

        return view('admin-views.vehicle.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'color_id' => 'required',
            'model' => 'required|string|max:255',
            'car_number' => 'required|string|max:255',
            'tech_passport_number' => 'required|string|max:255',
            'seats' => 'required|integer',
            'status' => 'required',
        ]);

        Vehicle::create([
            'user_id' => $request->user_id,
            'color_id' => $request->color_id,
            'model' => $request->model,
            'car_number' => $request->car_number,
            'tech_passport_number' => $request->tech_passport_number,
            'seats' => $request->seats,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Vehicle created successfully');
    }

    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $drivers = User::where('role', 'driver')
            ->orderBy('district_id', 'ASC')
            ->get();

        return view('admin-views.vehicle.edit', compact('vehicle', 'drivers'));
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $request->validate([
            'user_id' => 'required',
            'color_id' => 'required',
            'model' => 'required',
            'car_number' => 'required',
            'tech_passport_number' => 'required',
            'seats' => 'required',
            'status' => 'required',
        ]);

        $vehicle->update($request->all());

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Vehicle updated successfully');
    }

    public function destroy($id)
    {
        Vehicle::findOrFail($id)->delete();

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Vehicle deleted successfully');
    }
}
