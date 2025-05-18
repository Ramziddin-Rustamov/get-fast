@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="pb-2">
        <h4 class="fw-bold text-success mb-3">{{ __('My Parcel Bookings') }}</h4>
    </div>

    @if($clientBookedParcels->isEmpty())
        <div class="alert alert-info text-center">
            {{ __('You have not booked any parcels yet.') }}
        </div>
    @else
        @foreach($clientBookedParcels as $index => $booking)
            <div class="card shadow-sm mb-4 border border-primary">
                <div class="card-body">
                    <h5 class="fw-bold text-primary">{{ __('Booking #') }}{{ $index + 1 }}</h5>
                    
                    <p><strong>{{ __('Parcel Name:') }}</strong> {{ optional($booking->parcel)->name ?? __('N/A') }}</p>
                    <p><strong>{{ __('Weight:') }}</strong> {{ $booking->weight }} kg</p>
                    <p><strong>{{ __('Total Price:') }}</strong> {{ number_format($booking->total_price) }} so'm</p>
                    <p>
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
                    </p>

                    <hr>

                    @if($booking->parcel && $booking->parcel->trip)
                        @php
                            $trip = $booking->parcel->trip;
                            $driver = $trip->driver;
                        @endphp

                        <h6 class="fw-bold text-success">{{ __('Trip Info') }}</h6>
                        <p><strong>{{ __('Start Time:') }}</strong> {{ \Carbon\Carbon::parse($trip->start_time)->format('Y-m-d H:i') }}</p>
                        <p><strong>{{ __('End Time:') }}</strong> {{ \Carbon\Carbon::parse($trip->end_time)->format('Y-m-d H:i') }}</p>

                        <p><strong>{{ __('From:') }}</strong> 
                            {{ $trip->startQuarter->district->region->name }} > 
                            {{ $trip->startQuarter->district->name }} > 
                            {{ $trip->startQuarter->name }}
                        </p>
                        <p><strong>{{ __('To:') }}</strong> 
                            {{ $trip->endQuarter->district->region->name }} > 
                            {{ $trip->endQuarter->district->name }} > 
                            {{ $trip->endQuarter->name }}
                        </p>

                        <hr>

                        <h6 class="fw-bold text-info">{{ __('Driver Info') }}</h6>
                        <div class="d-flex align-items-center mb-2">
                            <img src="{{ asset('image/' . $driver->image) }}" class="rounded-circle me-2" width="50" height="50" alt="Driver">
                            <div>
                                <p class="mb-0 fw-bold">{{ $driver->name }}</p>
                                <p class="mb-0"><a href="tel:+{{ $driver->phone }}">{{ $driver->phone }}</a></p>
                            </div>
                        </div>
                        <p><strong>{{ __('Driver Region:') }}</strong> {{ $driver->region->name }}</p>
                        <p><strong>{{ __('Driver District:') }}</strong> {{ $driver->district->name }}</p>
                        <p><strong>{{ __('Driver Quarter:') }}</strong> {{ $driver->quarter->name }}</p>
                    @else
                        <div class="alert alert-secondary">
                            {{ __('Trip or driver information not available.') }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
