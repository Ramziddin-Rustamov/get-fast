<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\V1\Booking as Order;

class OrderController extends Controller
{
    /**
     * Buyurtmalar ro‘yxatini chiqarish
     */
    public function index()
    {
        $orders = Order::all();
        return view('orders.index', compact('orders'));
    }

    /**
     * Yangi buyurtma qo'shish formasi
     */
    public function create()
    {
        return view('orders.create');
    }

    /**
     * Yangi buyurtmani saqlash
     */
    public function store(Request $request)
    {
        $request->validate([
            'trip_id' => 'required|integer',
            'user_id' => 'required|integer',
            'seats_booked' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,confirmed,canceled',
        ]);

        Order::create([
            'trip_id' => $request->trip_id,
            'user_id' => $request->user_id,
            'seats_booked' => $request->seats_booked,
            'total_price' => $request->total_price,
            'status' => $request->status,
        ]);

        return redirect()->route('orders.index')->with('success', 'Order added successfully!');
    }

    /**
     * Bitta buyurtma ma'lumotlarini ko'rsatish
     */
    public function show(Order $order)
    {
        return view('orders.show', compact('order'));
    }

    /**
     * Buyurtmani tahrirlash formasi
     */
    public function edit(Order $order)
    {
        return view('orders.edit', compact('order'));
    }

    /**
     * Buyurtmani yangilash
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'trip_id' => 'required|integer',
            'user_id' => 'required|integer',
            'seats_booked' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,confirmed,canceled',
        ]);

        $order->update([
            'trip_id' => $request->trip_id,
            'user_id' => $request->user_id,
            'seats_booked' => $request->seats_booked,
            'total_price' => $request->total_price,
            'status' => $request->status,
        ]);

        return redirect()->route('orders.index')->with('success', 'Order updated successfully!');
    }

    /**
     * Buyurtmani o'chirish
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order deleted successfully!');
    }
}
