<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User as Client;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    /**
     * Clients List
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $clients = Client::where('role', 'client')
            ->with(['balance', 'bookings'])
            ->when($search, function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            })
            ->when($status, function ($q) use ($status) {
                $q->where('is_verified', $status);
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin-views.clients.index', compact('clients', 'search', 'status'));
    }

    /**
     * Create client form
     */
    public function create()
    {
        return view('admin-views.clients.create');
    }

    /**
     * Store new client
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'nullable|string|max:255',
            'phone'        => 'required|unique:users,phone',
            'password'     => 'required|min:6',
        ]);

        $client = Client::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
            'role'       => 'client',
            'is_verified' => true,
            'verification_status' => 'approved',
        ]);

        return redirect()->route('clients.index')->with('success', 'Client muvaffaqiyatli qo‘shildi!');
    }

    /**
     * Show client details
     */
    public function show($client)
    {

        $client = Client::where('role', 'client')
            ->with(['balance', 'bookings'])
            ->find($client);

        if (!$client) {
            return redirect()->route('clients.index')->with('error', 'Mijoz topilmadi!');
        }

        $trips = $client->bookings()->orderBy('id', 'desc')->paginate(5);

        // Paginate qilingan buyurtmalar
        $bookings = $client->bookings()->orderBy('id', 'desc')->paginate(4);

        // Paginate qilingan tranzaksiyalar
        $balanceTransactions = $client->balanceTransactions()->orderBy('created_at', 'desc')->paginate(5);


        return view('admin-views.clients.show', compact(
            'client',
            'trips',
            'balanceTransactions',
            'bookings'
        ));
    }

    /**
     * Edit page
     */
    public function edit($client)
    {
        $client = Client::where('role', 'client')->find($client);

        if (!$client) {
            return redirect()->route('clients.index')->with('error', 'Client topilmadi!');
        }

        return view('admin-views.clients.edit', compact('client'));
    }

    /**
     * Update client
     */
    public function update(Request $request, $client)
    {
        $client = Client::where('role', 'client')->find($client);
        if (!$client) {
            return redirect()->route('clients.index')->with('error', 'Client topilmadi!');
        }

        $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'nullable|string',
            'phone'      => 'required|unique:users,phone,' . $client->id,
        ]);

        $client->update([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'phone'      => $request->phone,
        ]);

        return redirect()->route('clients.show', $client->id)->with('success', 'Client yangilandi!');
    }

    /**
     * Delete client
     */
    public function destroy($client)
    {
        $client = Client::where('role', 'client')->find($client);

        if (!$client) {
            return redirect()->route('clients.index')->with('error', 'Client topilmadi!');
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client o‘chirildi!');
    }

    /**
     * Client trips
     */
    public function trips($client)
    {
        $client = Client::where('role', 'client')->findOrFail($client);
        $trips = $client->bookings()->orderBy('id', 'desc')->paginate(10);

        return view('admin-views.clients.trips', compact('client', 'trips'));
    }

    /**
     * Client balance transactions
     */
    public function balance($client)
    {
        $client = Client::where('role', 'client')->with('balance')->findOrFail($client);
        $balanceTransactions = $client->balanceTransactions()->orderBy('created_at', 'desc')->paginate(10);

        return view('admin-views.clients.balance', compact('client', 'balanceTransactions'));
    }

    /**
     * Client images (agar ishlatilsa)
     */
    public function images($client)
    {
        $client = Client::where('role', 'client')->with('images')->findOrFail($client);

        return view('admin-views.clients.images', compact('client'));
    }

    /**
     * Clientga SMS yuborish
     */
    public function sendSms(Request $request, $client)
    {
        $client = Client::where('role', 'client')->findOrFail($client);

        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $message = $request->input('message');

        // Bu yerda sms provider chaqiriladi
        // SmsService::send($client->phone, $message);

        return back()->with('success', 'SMS muvaffaqiyatli yuborildi!');
    }
}
