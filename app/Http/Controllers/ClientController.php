<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User as Client;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::where('role', 'client')->get();
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:clients,phone',
            'password' => 'nullable|string|min:6',
            'image' => 'nullable|string|max:255',
            'region_id' => 'nullable|string|max:20',
            'district_id' => 'nullable|string|max:255',
            'quarter_id' => 'nullable|string|max:255',
            'home' => 'nullable|string|max:255',
        ]);

        Client::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => $request->password ? Hash::make($request->password) : null,
            'image' => $request->image ?? 'default.jpg',
            'region_id' => $request->region_id,
            'district_id' => $request->district_id,
            'quarter_id' => $request->quarter_id,
            'home' => $request->home,
            'role' => 'client',
        ]);

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:clients,phone,' . $client->id,
            'password' => 'nullable|string|min:6',
            'image' => 'nullable|string|max:255',
            'region_id' => 'nullable|string|max:20',
            'district_id' => 'nullable|string|max:255',
            'quarter_id' => 'nullable|string|max:255',
            'home' => 'nullable|string|max:255',
        ]);

        $client->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => $request->password ? Hash::make($request->password) : $client->password,
            'image' => $request->image ?? $client->image,
            'region_id' => $request->region_id,
            'district_id' => $request->district_id,
            'quarter_id' => $request->quarter_id,
            'home' => $request->home,
        ]);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}
