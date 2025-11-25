@extends('layouts.app')

@section('title', 'Booking Details')

@section('content')
<div class="container py-4">

    <h2 class="mb-4 text-center">🧾 Booking Details</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            Booking Information
        </div>

        <div class="card-body">

            <table class="table table-bordered mb-0">
                <tr>
                    <th>ID</th>
                    <td>{{ $order->id }}</td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge
                            @if($order->status=='pending') bg-secondary
                            @elseif($order->status=='confirmed') bg-success
                            @elseif($order->status=='cancelled') bg-danger text-white
                            @endif
                        ">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Total Price</th>
                    <td>{{ number_format($order->total_price,2) }} so'm</td>
                </tr>

                <tr>
                    <th>Seats Booked</th>
                    <td>{{ $order->seats_booked }}</td>
                </tr>

                <tr>
                    <th>Booking Created</th>
                    <td>{{ $order->created_at }}</td>
                </tr>

                <tr>
                    <th>Expired At</th>
                    <td>{{ $order->expired_at }}</td>
                </tr>

                <tr>
                    <th>Departed At</th>
                    <td>{{ $order->departed_at ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- USER INFO --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            Client Information
        </div>
        <div class="card-body">

            @if($order->user)
            <table class="table table-bordered mb-0">
                <tr>
                    <th>Full Name</th>
                    <td>{{ $order->user->first_name ?? '' }} {{ $order->user->last_name ?? '' }}</td>
                </tr>

                <tr>
                    <th>Phone</th>
                    <td>{{ $order->user->phone }}</td>
                </tr>

                <tr>
                    <th>Role</th>
                    <td>{{ ucfirst($order->user->role) }}</td>
                </tr>
            </table>
            @else
                <p class="text-muted">No user data</p>
            @endif

        </div>
    </div>

    {{-- TRIP INFO --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            Trip Information
        </div>

        <div class="card-body">
            @if($order->trip)
            <table class="table table-bordered mb-0">
                <tr>
                    <th>Trip ID</th>
                    <td>{{ $order->trip->id }}</td>
                </tr>

                <tr>
                    <th>Driver</th>
                    <td>
                        {{ $order->trip->driver->first_name ?? '' }}
                        {{ $order->trip->driver->last_name ?? '' }} <br>
                        <small class="text-muted">{{ $order->trip->driver->phone }}</small>
                    </td>
                </tr>

                <tr>
                    <th>From</th>
                    <td>{{ $order->trip->startQuarter->name ?? 'N/A' }}</td>
                </tr>

                <tr>
                    <th>To</th>
                    <td>{{ $order->trip->endQuarter->name ?? 'N/A' }}</td>
                </tr>

                <tr>
                    <th>Start Time</th>
                    <td>{{ $order->trip->start_time }}</td>
                </tr>

                <tr>
                    <th>End Time</th>
                    <td>{{ $order->trip->end_time }}</td>
                </tr>
            </table>
            @else
                <p class="text-muted">Trip not found</p>
            @endif
        </div>
    </div>

    {{-- PASSENGERS --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            Passenger List
        </div>

        <div class="card-body">
            @if($order->passengers && count($order->passengers) > 0)
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Passenger Name</th>
                            <th>Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->passengers as $index => $p)
                            <tr>
                                <td>{{ $index+1 }}</td>
                                <td>{{ $p->name }}</td>
                                <td>{{ $p->phone }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">No passengers</p>
            @endif
        </div>
    </div>

    {{-- BACK BUTTON --}}
    <div class="text-center mt-3">
        <a href="{{ route('orders.index') }}" class="btn btn-dark px-4">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

</div>
@endsection
