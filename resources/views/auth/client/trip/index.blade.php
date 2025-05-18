@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="pb-2 mb-3">
        <h4 class="fw-bold text-success">{{ __('My Trips') }}</h4>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="list-group">
        @if($booking)
            @foreach($booking as $booking)
                <div class="card shadow-sm border border-primary mb-4">
                    <div class="card-body">
                        
                        @if ($booking->status === 'pending')
                            <div class="mb-2">
                                <span class="fw-bold fs-6 text-success">
                                    {{ __('Pending') }} <i class="fas fa-sync fa-spin"></i>
                                </span>
                            </div>
                        @endif
                        @if ($booking->status === 'confirmed')
                            <div class="mb-2">
                                <span class="fw-bold fs-6 text-success">
                                    {{ __('Confirmed') }} <i class="fas fa-check"></i>
                                </span>
                            </div>
                        @endif
                        @if ($booking->status === 'cancelled')
                            <div class="mb-2">
                                <span class="fw-bold fs-6 text-success">
                                    {{ __('Cancelled') }} <i class=" fas fa-cog fa-spin"></i>
                                </span>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mb-2"> 

                            <span class="fw-bold fs-5">{{ \Carbon\Carbon::parse($booking->trip->end_time) }} </span>
                            <span class="fas fa-arrow-right"></span>
                            <span class="fw-bold fs-5">
                               
                                {{ \Carbon\Carbon::parse($booking->trip->start_time)->diff(\Carbon\Carbon::parse($booking->trip->end_time))->h }} h
                                {{ \Carbon\Carbon::parse($booking->trip->start_time)->diff(\Carbon\Carbon::parse($booking->trip->end_time))->i }} m
                            </span>
                            <span class="fas fa-arrow-right"></span> 
                            <span class="fw-bold fs-5">{{ \Carbon\Carbon::parse($booking->trip->end_time) }} </span>
                        </div>

                        <hr>

                        <p class="fw-bold fs-5 mb-1">
                            {{ __('From') }} <i class="fas fa-map-marker-alt"></i>
                        </p>
                        <div class="text-success mb-2">
                            <strong>
                                {{ $booking->trip->startQuarter->district->region->name }} <i class="fas fa-arrow-right"></i>
                                {{ $booking->trip->startQuarter->district->name }} <i class="fas fa-arrow-right"></i>
                                {{ $booking->trip->startQuarter->name }}
                            </strong>
                        </div>

                        <p class="fw-bold fs-5 mb-1">
                            {{ __('To') }} <i class="fas fa-map-marker-alt"></i>
                        </p>
                        <div class="text-success mb-3">
                            <strong>
                                {{ $booking->trip->endQuarter->district->region->name }} <i class="fas fa-arrow-right"></i>
                                {{ $booking->trip->endQuarter->district->name }} <i class="fas fa-arrow-right"></i>
                                {{ $booking->trip->endQuarter->name }}
                            </strong>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <strong>{{ __('Price per seat:') }}</strong>
                                <strong class="text-dark">{{ number_format($booking->trip->price_per_seat) }} So'm</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <strong>{{ __('Available Seats:') }}</strong>
                                <strong class="text-success">{{ $booking->trip->available_seats }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <strong>{{ __('Total Seats:') }}</strong>
                                <strong class="text-danger">{{ $booking->trip->total_seats }}</strong>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <h5>{{ __('Your Ordered Place and Time') }}</h5>
                            <div class="d-flex flex-column gap-1">
                                <div>{{ __('Your booked place:') }} {{ $booking->seats_booked }}</div>
                                <div>{{ __('Your paid:') }} {{ $booking->trip->price_per_seat * $booking->seats_booked }} So'm</div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex align-items-center mb-2">
                            <img src="{{ asset('image') }}/{{ $booking->trip->driver->image }}" class="rounded-circle me-2" width="40" height="40" alt="Driver">
                            <strong>{{ $booking->trip->driver->name }}</strong>
                            <span class="ms-auto">⭐ 4.9</span>
                        </div>

                        <div class="ms-2">
                            <strong><a href="tel:+{{ $booking->trip->driver->phone }}">{{ $booking->trip->driver->phone }}</a></strong><br>
                            <strong>{{ $booking->trip->driver->region->name }}</strong><br>
                            <strong>{{ $booking->trip->driver->district->name }}</strong><br>
                            <strong>{{ $booking->trip->driver->quarter->name }}</strong>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <p class="text-center text-muted">{{ __('You have no trips yet') }}</p>
        @endif
    </div>
</div>
@endsection
