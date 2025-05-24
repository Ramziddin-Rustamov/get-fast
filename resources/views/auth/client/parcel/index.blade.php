@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">{{ __('📦 My Parcel Bookings') }}</h3>
        <p class="text-muted">{{ __('All your booked parcels appear here.') }}</p>
    </div>

    @if($clientBookedParcels->isEmpty())
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle"></i> {{ __('You have not booked any parcels yet.') }}
        </div>
    @else
    <div class="row g-4">
        @foreach($clientBookedParcels as $index => $booking)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow rounded-4 h-100">
                <div class="card-body">
                    <h5 class="fw-bold text-success mb-3">
                        {{ __('📌 Booking #') }}{{ $index + 1 }}
                    </h5>

                    <ul class="list-unstyled mb-3">
                        <li><strong>{{ __('Parcel Name:') }}</strong> {{ optional($booking->parcel)->name ?? __('N/A') }}</li>
                        <li><strong>{{ __('Weight:') }}</strong> {{ $booking->weight }} kg</li>
                        <li><strong>{{ __('Total Price:') }}</strong> <span class="text-primary">{{ number_format($booking->total_price) }} so'm</span></li>
                        <li>
                            <strong>{{ __('Status:') }}</strong>
                            @switch($booking->status)
                                @case('pending')
                                    <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                                    @break
                                @case('confirmed')
                                    <span class="badge bg-success">{{ __('Confirmed') }}</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-danger">{{ __('Cancelled') }}</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ ucfirst($booking->status) }}</span>
                            @endswitch
                        </li>
                    </ul>

                    @if($booking->parcel && $booking->parcel->trip)
                        @php
                            $trip = $booking->parcel->trip;
                            $driver = $trip->driver;
                        @endphp

                        <div class="mb-3">
                            <h6 class="fw-bold text-info">{{ __('🛣 Trip Info') }}</h6>
                            <p class="mb-1"><strong>{{ __('Start Time:') }}</strong> {{ \Carbon\Carbon::parse($trip->start_time)->format('Y-m-d H:i') }}</p>
                            <p class="mb-1"><strong>{{ __('End Time:') }}</strong> {{ \Carbon\Carbon::parse($trip->end_time)->format('Y-m-d H:i') }}</p>
                            <p class="mb-1"><strong>{{ __('From:') }}</strong> {{ $trip->startQuarter->district->region->name }} > {{ $trip->startQuarter->district->name }} > {{ $trip->startQuarter->name }}</p>
                            <p><strong>{{ __('To:') }}</strong> {{ $trip->endQuarter->district->region->name }} > {{ $trip->endQuarter->district->name }} > {{ $trip->endQuarter->name }}</p>
                        </div>

                        <hr>

                        <div class="mb-2">
                            <h6 class="fw-bold text-dark">{{ __('👨‍✈️ Driver Info') }}</h6>
                            <div class="d-flex align-items-center mb-2">
                                <img src="{{ asset('image/' . $driver->image) }}" alt="Driver" class="rounded-circle border border-2 border-primary me-3" width="50" height="50">
                                <div>
                                    <div class="fw-bold">{{ $driver->name }}</div>
                                    <a href="tel:+{{ $driver->phone }}" class="text-decoration-none">{{ $driver->phone }}</a>
                                </div>
                            </div>
                            <div class="small text-muted">
                                <p class="mb-1"><strong>{{ __('Region:') }}</strong> {{ $driver->region->name }}</p>
                                <p class="mb-1"><strong>{{ __('District:') }}</strong> {{ $driver->district->name }}</p>
                                <p><strong>{{ __('Quarter:') }}</strong> {{ $driver->quarter->name }}</p>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-secondary mt-3">
                            <i class="fas fa-exclamation-circle"></i> {{ __('Trip or driver information not available.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
