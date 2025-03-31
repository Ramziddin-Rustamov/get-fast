<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\V1\Booking;

class OrderController extends Controller
{
    /**
     * Buyurtmalar ro‘yxatini chiqarish
     */
    public function index()
    {
        $orders = Booking::all();
        return view('admin-views.orders.index', compact('orders'));
    }

    /**
     * Yangi buyurtma qo'shish formasi
     */
    public function create()
    {
        return view('admin-views.orders.create');
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

        Booking::create([
            'trip_id' => $request->trip_id,
            'user_id' => $request->user_id,
            'seats_booked' => $request->seats_booked,
            'total_price' => $request->total_price,
            'status' => $request->status,
        ]);

        return redirect()->route('orders.index')->with('success', 'Booking added successfully!');
    }

    /**
     * Bitta buyurtma ma'lumotlarini ko'rsatish
     */
    public function show(Booking $order)
    {
        return view('admin-views.orders.show', compact('order'));
    }

    /**
     * Buyurtmani tahrirlash formasi
     */
    public function edit(Booking $order)
    {
        return view('admin-views.orders.edit', compact('order'));
    }

    /**
     * Buyurtmani yangilash
     */
    public function update(Request $request, Booking $order)
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

        return redirect()->route('orders.index')->with('success', 'Booking updated successfully!');
    }

    /**
     * Buyurtmani o'chirish
     */
    public function destroy(Booking $order)
    {
        $order->delete();
        return redirect()->route('admin-views.orders.index')->with('success', 'Booking deleted successfully!');
    }
}
