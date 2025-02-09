@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
    <h1>Order Details</h1>
    
    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <td>{{ $order->id }}</td>
        </tr>
        <tr>
            <th>Trip ID</th>
            <td>{{ $order->trip_id }}</td>
        </tr>
        <tr>
            <th>User ID</th>
            <td>{{ $order->user_id }}</td>
        </tr>
        <tr>
            <th>Seats Booked</th>
            <td>{{ $order->seats_booked }}</td>
        </tr>
        <tr>
            <th>Total Price</th>
            <td>{{ $order->total_price }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ ucfirst($order->status) }}</td>
        </tr>
        <tr>
            <th>Created At</th>
            <td>{{ $order->created_at }}</td>
        </tr>
        <tr>
            <th>Updated At</th>
            <td>{{ $order->updated_at }}</td>
        </tr>
    </table>

    <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-warning">Edit</a>
    <a href="{{ route('orders.index') }}" class="btn btn-secondary">Back to List</a>

    <form action="{{ route('orders.destroy', $order->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
@endsection
