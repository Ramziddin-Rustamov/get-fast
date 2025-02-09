<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'admin')->get();
        return view('admins.index', compact('admins'));
    }

    public function create()
    {
        return view('admins.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:users,phone',
            'password' => 'required|string|min:6',
            'image' => 'nullable|string|max:255',
            'region_id' => 'nullable|string|max:20',
            'district_id' => 'nullable|string|max:255',
            'quarter_id' => 'nullable|string|max:255',
            'home' => 'nullable|string|max:255',
        ]);

        User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'image' => $request->image ?? 'default.jpg',
            'region_id' => $request->region_id,
            'district_id' => $request->district_id,
            'quarter_id' => $request->quarter_id,
            'home' => $request->home,
            'role' => 'admin',
        ]);

        return redirect()->route('admins.index')->with('success', 'Admin created successfully.');
    }

    public function show(User $admin)
    {
        return view('admins.show', compact('admin'));
    }

    public function edit(User $admin)
    {
        return view('admins.edit', compact('admin'));
    }

    public function update(Request $request, User $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:users,phone,' . $admin->id,
            'password' => 'nullable|string|min:6',
            'image' => 'nullable|string|max:255',
            'region_id' => 'nullable|string|max:20',
            'district_id' => 'nullable|string|max:255',
            'quarter_id' => 'nullable|string|max:255',
            'home' => 'nullable|string|max:255',
        ]);

        $admin->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => $request->password ? Hash::make($request->password) : $admin->password,
            'image' => $request->image ?? $admin->image,
            'region_id' => $request->region_id,
            'district_id' => $request->district_id,
            'quarter_id' => $request->quarter_id,
            'home' => $request->home,
        ]);

        return redirect()->route('admins.index')->with('success', 'Admin updated successfully.');
    }

    public function destroy(User $admin)
    {
        $admin->delete();
        return redirect()->route('admins.index')->with('success', 'Admin deleted successfully.');
    }
}
