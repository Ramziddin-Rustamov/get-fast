@extends('layouts.app')

@section('content')

@guest
    <div class="d-flex justify-content-center align-items-center vh-100 text-center">
        <div>
            <h1 class="mb-4">Assalomu alaykum!</h1>
            <h3 class="mb-4">Kim bo‘lib ro‘yxatdan o‘tasiz?</h3>

            <div class="d-flex justify-content-center gap-4 flex-wrap">
                <a href="{{ route('client.auth.register.index') }}" class="btn btn-primary btn-lg px-5 py-3">
                    <i class="fas fa-user"></i> Mijoz
                </a>
                <a href="{{ route('driver.auth.register.index') }}" class="btn btn-success btn-lg px-5 py-3">
                    <i class="fas fa-car"></i> Haydovchi
                </a>
                <a href="{{ route('auth.login.index') }}" class="btn btn-warning btn-lg px-5 py-3">
                    <i class="fas fa-sign-in-alt"></i> Kirish
                </a>
            </div>
        </div>
    </div>
@endguest

@auth
    <div class="container mt-4">
        {{-- @can('driver_web')
            <h6>{{ __('Hi,') }} {{ __('Driver') }} -  {{ auth()->user()->name }} </h6>
        @endcan
        @can('client_web')
            <h6>{{ __('Hi,') }}  {{ __('Client') }} - {{ auth()->user()->name }}</h6>
        @endcan
        @can('admin')
            <h6 class="underline">{{ __('Hi,') }} {{ __('Admin') }} - {{ auth()->user()->name }} </h6>
        @endcan --}}

       

        @if($trips->count() > 0)
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="fw-bold"> Today {{ date('d F Y') }} </h4>
            <button class="btn btn-primary px-4 my-2" data-bs-toggle="modal" data-bs-target="#searchModal">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
            <div class="row">
                @foreach ($trips as $trip)
                    <div class="col-md-4 col-lg-6 mb-4">
                        <a href="{{ route('trip.show', $trip->id) }}" class="text-decoration-none">
                            <div class="trip-card p-4 shadow rounded bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <!-- From Location -->
                                    <div class="text-center">
                                        <span class="text-muted"><i class="fas fa-map-marker-alt text-danger"></i> From</span>
                                        <h3 class="text-primary">{{ date('H:i', strtotime($trip->start_time)) }}</h3>
                                        <span class="text-primary">{{ date('d F Y', strtotime($trip->start_time)) }}</span><br>
                                        <span>{{ $trip->startQuarter->district->region->name }},</span>
                                        <span>{{ $trip->startQuarter->district->name }},</span>
                                        <strong>{{ $trip->startQuarter->name }}</strong>
                                    </div>

                                    <!-- Duration -->
                                    <div class="text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i>
                                            {{ gmdate("H:i", strtotime($trip->end_time) - strtotime($trip->start_time)) }} 
                                            <br> 
                                            <i class="fas fa-route"></i>
                                        </small>
                                    </div>

                                    <!-- To Location -->
                                    <div class="text-center">
                                        <span class="text-muted"><i class="fas fa-map-marker-alt text-success"></i> To</span>
                                        <h3 class="text-primary">{{ date('H:i', strtotime($trip->end_time)) }}</h3>
                                        <span class="text-primary">{{ date('d F Y', strtotime($trip->end_time)) }}</span><br>
                                        <span>{{ $trip->endQuarter->district->region->name }},</span>
                                        <span>{{ $trip->endQuarter->district->name }},</span>
                                        <strong>{{ $trip->endQuarter->name }}</strong>
                                    </div>
                                </div>

                                <hr>

                              <!-- Driver Info -->
                            <div class="d-flex align-items-center p-2 border rounded shadow-sm bg-light">
                                <img src="{{ asset('image/' . $trip->driver->image) }}" width="50" height="50" class="rounded-circle me-3 border" alt="Driver">
                                
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ $trip->driver->name }}</div>
                                    <div class="text-muted small">📱 {{ substr($trip->driver->phone, 0, 5) . '***' . substr($trip->driver->phone, -2) }}</div>
                                    <div class="text-warning small"><i class="fas fa-star me-1"></i> 4.9</div>
                                </div>

                                <div class="text-end">
                                    <span class="badge bg-warning text-dark fs-6"><i class="fas fa-star"></i> 5.0</span>
                                </div>
                            </div>


                                <!-- Trip Details -->
                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-success">
                                        <i class="fas fa-coins"></i> {{ number_format($trip->price_per_seat, 0, ',', ' ') }} UZS
                                    </span>
                                    <span class="badge bg-info text-dark">
                                        <i class="fas fa-chair"></i> {{ $trip->available_seats }} / {{ $trip->total_seats }}
                                    </span>
                                </div>

                                <!-- Parcel Info -->
                                @if($trip->parcels && count($trip->parcels))
                                    <hr>
                                    <div>
                                        <h6 class="text-muted"><i class="fas fa-box-open"></i> {{ __('Available Parcels') }}</h6>
                                        @foreach ($trip->parcels as $parcel)
                                            <div class="card mb-2 shadow-sm border-0">
                                                <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $parcel->max_weight }} kg max</strong><br>
                                                        <small class="text-muted">1kg = {{ number_format($parcel->price_per_kg, 0, ',', ' ') }} UZS</small>
                                                    </div>
                                                    @if($parcel->max_weight > 0)
                                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('client.parcel.show', $parcel->id) }}">
                                                            <i class="fas fa-box"></i> {{ __('Book Parcel') }}
                                                        </a>
                                                    @else
                                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                                            {{ __('No space for parcels') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <hr>

                                @if($trip->available_seats > 0)
                                    <a href="{{ route('trip.show', $trip->id) }}" class="btn btn-primary w-100">
                                        <i class="fas fa-chair"></i> {{ __('Book Trip') }}
                                    </a>
                                @else
                                    <button class="btn btn-danger w-100" disabled>
                                        <i class="fas fa-ban"></i> {{ __('No Seats Available') }}
                                    </button>
                                @endif
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center mt-5">
                <h3 class="text-muted">No active trips available.</h3>
            </div>
        @endif
    </div>

    <!-- Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalLabel">Search for a Trip</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="fromLocation" class="form-label">From</label>
                            <input type="text" class="form-control" id="fromLocation" placeholder="Enter departure city">
                        </div>
                        <div class="mb-3">
                            <label for="toLocation" class="form-label">To</label>
                            <input type="text" class="form-control" id="toLocation" placeholder="Enter destination city">
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endauth

@endsection

@push('styles')
<style>
    a {
        text-decoration: none !important;
    }

    body {
        background-color: #f8f9fa;
    }

    .trip-card {
        background: white;
        border-radius: 12px;
    }
</style>
@endpush
