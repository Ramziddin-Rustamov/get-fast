@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h3 class="fw-bold text-success">
            <i class="fas fa-car-side me-2"></i> {{ __('My Trips') }} ({{ $booking->count() }})
        </h3>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        @forelse ($booking as $booking)
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow rounded-4 h-100">
                <div class="card-body d-flex flex-column justify-content-between">

                    {{-- Status --}}
                    <div class="mb-3">
                        @if ($booking->status === 'pending')
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="fas fa-clock me-1"></i> {{ __('Pending') }}
                            </span>
                        @elseif ($booking->status === 'confirmed')
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-check me-1"></i> {{ __('Confirmed') }}
                            </span>
                        @elseif ($booking->status === 'cancelled')
                            <span class="badge bg-danger fs-6">
                                <i class="fas fa-times me-1"></i> {{ __('Cancelled') }}
                            </span>
                        @endif
                    </div>

                    {{-- Time --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-center">
                            <h5 class="fw-bold mb-0 text-primary">{{ \Carbon\Carbon::parse($booking->trip->start_time)->format('H:i') }}</h5>
                            <small class="text-muted">{{ __('Start') }}</small>
                        </div>
                        <div class="text-center">
                            <i class="fas fa-arrow-right fa-lg text-secondary"></i>
                            <div class="text-muted small">
                                {{ \Carbon\Carbon::parse($booking->trip->start_time)->diff(\Carbon\Carbon::parse($booking->trip->end_time))->format('%h h %i m') }}
                            </div>
                        </div>
                        <div class="text-center">
                            <h5 class="fw-bold mb-0 text-danger">{{ \Carbon\Carbon::parse($booking->trip->end_time)->format('H:i') }}</h5>
                            <small class="text-muted">{{ __('End') }}</small>
                        </div>
                    </div>

                    {{-- Locations --}}
                    <div class="mb-3">
                        <p class="fw-bold mb-1 text-muted"><i class="fas fa-map-marker-alt me-1"></i> {{ __('From') }}</p>
                        <div class="text-success">{{ $booking->trip->startQuarter->district->region->name }} → {{ $booking->trip->startQuarter->district->name }} → {{ $booking->trip->startQuarter->name }}</div>
                    </div>
                    <div class="mb-3">
                        <p class="fw-bold mb-1 text-muted"><i class="fas fa-map-marker-alt me-1"></i> {{ __('To') }}</p>
                        <div class="text-success">{{ $booking->trip->endQuarter->district->region->name }} → {{ $booking->trip->endQuarter->district->name }} → {{ $booking->trip->endQuarter->name }}</div>
                    </div>

                    {{-- Price and Seats --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('Price per seat') }}:</span>
                            <span class="fw-bold text-dark">{{ number_format($booking->trip->price_per_seat) }} So'm</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('Available Seats') }}:</span>
                            <span class="fw-bold text-success">{{ $booking->trip->available_seats }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('Total Seats') }}:</span>
                            <span class="fw-bold text-danger">{{ $booking->trip->total_seats }}</span>
                        </div>
                    </div>

                    {{-- User Booking Info --}}
                    <div class="mb-3">
                        <h6 class="fw-bold text-muted">{{ __('Your Booking') }}</h6>
                        <div>{{ __('Seats Booked') }}: <strong>{{ $booking->seats_booked }}</strong></div>
                        <div>{{ __('Total Paid') }}: <strong>{{ $booking->trip->price_per_seat * $booking->seats_booked }} So'm</strong></div>
                    </div>

                    {{-- Driver Info --}}
                    <div class="d-flex align-items-center mb-2">
                        <img src="{{ asset('image/' . $booking->trip->driver->image) }}" class="rounded-circle shadow-sm me-3" width="48" height="48" alt="Driver">
                        <div>
                            <strong>{{ $booking->trip->driver->name }}</strong><br>
                            <span class="text-muted small">⭐ 4.9</span>
                        </div>
                    </div>
                    <div class="ms-1">
                        <div><a href="tel:+{{ $booking->trip->driver->phone }}" class="text-decoration-none text-dark"><i class="fas fa-phone-alt me-1 text-success"></i>{{ $booking->trip->driver->phone }}</a></div>
                        <div class="small text-muted">
                            {{ $booking->trip->driver->region->name }},
                            {{ $booking->trip->driver->district->name }},
                            {{ $booking->trip->driver->quarter->name }},
                            {{ $booking->trip->driver->home }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @empty
            <div class="col-12">
                <p class="text-center text-muted fs-5"><i class="fas fa-info-circle me-1"></i> {{ __('You have no trips yet') }}</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
