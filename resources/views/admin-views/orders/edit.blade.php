@extends('layouts.app')

@section('title', 'Edit Order')

@section('content')
    <h1>Edit Order</h1>
    <form action="{{ route('orders.update', $order->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="trip_id" class="form-label">Trip ID</label>
            <input type="number" class="form-control" id="trip_id" name="trip_id" value="{{ $order->trip_id }}" required>
        </div>
        
        <div class="mb-3">
            <label for="user_id" class="form-label">User ID</label>
            <input type="number" class="form-control" id="user_id" name="user_id" value="{{ $order->user_id }}" required>
        </div>
        
        <div class="mb-3">
            <label for="seats_booked" class="form-label">Seats Booked</label>
            <input type="number" class="form-control" id="seats_booked" name="seats_booked" value="{{ $order->seats_booked }}" required>
        </div>
        
        <div class="mb-3">
            <label for="total_price" class="form-label">Total Price</label>
            <input type="number" step="0.01" class="form-control" id="total_price" name="total_price" value="{{ $order->total_price }}" required>
        </div>
        
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status">
                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }}>Canceled</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection
