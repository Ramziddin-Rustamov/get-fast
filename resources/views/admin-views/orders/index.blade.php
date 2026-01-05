@extends('layouts.app')

@section('title', 'Orders List')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-center">📦 Orders List</h2>

    {{-- Filter by status --}}
    <form method="GET" class="mb-3 d-flex justify-content-start gap-2">
        <select name="status" class="form-select w-auto">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
            <option value="confirmed" {{ request('status')=='confirmed'?'selected':'' }}>Confirmed</option>
            <option value="canceled" {{ request('status')=='canceled'?'selected':'' }}>Canceled</option>
            <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
        </select>
        
        <select name="date" class="form-select w-auto">
            <option value="">All Dates</option>
            <option value="today" {{ request('date')=='today'?'selected':'' }}>Today</option>
            <option value="week" {{ request('date')=='week'?'selected':'' }}>This Week</option>
            <option value="last_week" {{ request('date')=='last_week'?'selected':'' }}>Last Week</option>
        </select>
        
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Reset</a>
    </form>

    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-clock text-primary fs-5"></i>
        <span class="text-secondary fw-semibold fs-6">
            {{ \Carbon\Carbon::now()->locale('en')->translatedFormat('l, d F Y H:i') }}
        </span>
    </div>
    

    {{-- Orders Table --}}
    <div class="table-responsive shadow rounded">
        <table class="table table-bordered table-hover align-middle mb-0 text-center">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Trip</th>
                    <th>Client</th>
                    <th>Driver</th>
                    <th>Booked Seats</th>
                    <th>Total Price</th>
                    <th>Price P/Seat</th>
                    <th>Status</th>
                    <th>Start/End Time</th>
                    <th>Data/ C and U</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr>
                    <td>{{ $booking->id }}</td>
                    <td>
                        @if($booking->trip)
                        <div>
                            <strong>{{ $booking->trip->startQuarter->name ?? 'N/A' }} → {{ $booking->trip->endQuarter->name ?? 'N/A' }}</strong>
                            <br>
                            <small class="text-muted">{{ $booking->trip->vehicle->model ?? 'Vehicle N/A' }}</small>
                            <small class="text-muted">{{ $booking->trip->vehicle->car_number ?? 'Vehicle N/A' }}</small>
                        </div>
                        @else
                        <span class="text-muted">No Trip</span>
                        @endif
                    </td>
                    <td>
                        {{ $booking->user->first_name ?? '' }} {{ $booking->user->last_name ?? 'Unknown' }}<br>
                        <small class="text-muted">{{ $booking->user->phone ?? '-' }}</small>
                    </td>
                    <td>
                        {{ $booking->trip->driver->first_name ?? '' }} {{ $booking->trip->driver->last_name ?? 'Unknown' }}<br>
                        <small class="text-muted">{{ $booking->trip->driver->phone ?? '-' }}</small>
                    </td>
                    <td>{{ $booking->seats_booked }}</td>
                    <td>{{ number_format($booking->total_price, 2) }} UZS</td>
                    <td>{{ number_format($booking->trip->price_per_seat, 2) }} UZS</td>

                    <td>
                        <span class="badge
                            @if($booking->status == 'pending') bg-secondary
                            @elseif($booking->status == 'confirmed') bg-success
                            @elseif($booking->status == 'cancelled') bg-danger 
                            @endif
                        ">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </td>
                    

                    <td>
                        <small class="text-muted">Start At: {{ $booking->trip->start_time }}</small><br>
                        <small class="text-muted">End At: {{ $booking->trip->end_time }}</small><br>
                    </td>


                    <td>
                        <small class="text-muted">Cr At: {{ $booking->created_at->format('d.m.Y H:i') }}</small><br>
                        <small class="text-muted">Up At: {{ $booking->updated_at->format('d.m.Y H:i') }}</small>
                    </td>
                    <td>
                        <a href="{{ route('orders.show', $booking->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-muted">No orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $bookings->links() }}
    </div>
</div>

@endsection
