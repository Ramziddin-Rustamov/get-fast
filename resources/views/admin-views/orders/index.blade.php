@extends('layouts.app')

@section('title', 'Orders List')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-center">📦 Orders List</h2>

    {{-- Add New Order --}}
    <div class="mb-3 text-end">
        <a href="{{ route('orders.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus-circle"></i> Add New Order
        </a>
    </div>

    {{-- Orders Table --}}
    <div class="table-responsive shadow rounded">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Trip</th>
                    <th>Ordered by User</th>
                    <th>Booked Seats</th>
                    <th>Paid Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="text-center">{{ $order->id }}</td>

                        {{-- Trip Info --}}
                        <td>
                            @if($order->trip)
                                <div>
                                    <strong>From:</strong> {{ $order->trip->startQuarter->name ?? 'N/A' }}<br>
                                    <strong>To:</strong> {{ $order->trip->endQuarter->name ?? 'N/A' }}
                                </div>
                            @else
                                <span class="text-muted">No Trip</span>
                            @endif
                        </td>

                        {{-- User Info --}}
                        <td>
                            {{ $order->user->name ?? 'Unknown User' }} <br>
                            <small class="text-muted">{{ $order->user->phone}}</small>
                        </td>

                        <td class="text-center">{{ $order->seats_booked }}</td>
                        <td>{{ number_format($order->total_price, 2) }} so'm</td>
                        <td class="text-center">
                            <span class="badge 
                                @if($order->status == 'pending') bg-secondary
                                @elseif($order->status == 'confirmed') bg-success
                                @elseif($order->status == 'canceled') bg-danger
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="text-center">
                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('orders.destroy', $order->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this order?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- FontAwesome --}}
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
@endsection
