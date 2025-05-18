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

    <div class="container mt-4">
        {{-- <h4 class="fw-bold">Tomorrow</h4>
        <p>Warsaw, Poland → Berlin, Germany</p> --}}
        @endguest
    @auth
        @can('driver_web')
            <h6>{{ __('Hi,') }} {{ __('Driver') }} -  {{ auth()->user()->name }} </h6>
        @endcan
        @can('client_web')
            <h6>{{ __('Hi,') }}  {{ __('Client') }} - {{ auth()->user()->name }}</h6>
        @endcan

        @can('admin')
          <h6 class="underline">{{ __('Hi,') }} {{ __('Admin') }} - {{ auth()->user()->name }} </h6>
        @endcan

        <div class="d-flex justify-content-between align-items-center">
            <h4 class="fw-bold">Tomorrow</h4>
            <!-- Search Button -->
            <button class="btn btn-primary px-4 my-2" data-bs-toggle="modal" data-bs-target="#searchModal">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
        <!-- First Trip -->
        {{-- <div class="trip-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5>05:00 <i class="fas fa-circle"></i> Warsaw</h5>
                    <small>6:40</small>
                    <h5>11:40 <i class="fas fa-circle"></i> Berlin</h5>
                </div>
                <div class="price">143.00 PLN</div>
            </div>
            <hr>
            <div class="d-flex align-items-center">
                <img src="https://via.placeholder.com/50" class="rounded-circle me-2" alt="Driver">
                <span>Slawomir</span>
                <span class="ms-auto"><i class="fas fa-star text-warning"></i> 4.9</span>
            </div>
        </div> --}}
    
        <!-- Second Trip (Full) -->
        {{-- <div class="trip-card full-trip">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5>06:00 <i class="fas fa-circle"></i> Warsaw</h5>
                    <small>5:20</small>
                    <h5>11:20 <i class="fas fa-circle"></i> Rangsodrf</h5>
                </div>
                <div class="price">Full</div>
            </div>
            <hr>
            <div class="d-flex align-items-center">
                <img src="https://via.placeholder.com/50" class="rounded-circle me-2" alt="Driver">
                <span>Arkadiusz</span>
                <span class="ms-auto"><i class="fas fa-star text-warning"></i> 4.9</span>
            </div>
        </div> --}}
    
        <!-- Third Trip -->
        {{-- <div class="trip-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span> <i class="fas fa-map-marker"></i>   From </span>
                    <h3>07:20 <br></h3>
                    <h5>Warsaw</h5> <hr>
                    <small><i class="fas fa-clock"></i> 5:20 <i class="fas fa-route"></i> </small> <hr>
                    <span> <i class="fas fa-map-marker"></i> To </span>
                    <h3>07:20 <br></h3>
                    <h5>Warsaw</h5>
                </div>
            </div>
            <hr>
            <div class="d-flex align-items-center">
                <img src="https://via.placeholder.com/50" class="rounded-circle me-2" alt="Driver">
                <span>Damian</span>
                <span class="ms-auto"><i class="fas fa-star text-warning"></i> 5.0</span>
            </div>
            <div class="mt-2">
                <span class="no-service-fee">No service fee.</span>
            </div>
        </div> --}}

        @if($trips->count() > 0)
        <div class="container mt-4">
            <div class="row">
                @foreach ($trips as $trip)
                <a href="{{ route('trip.show', $trip->id) }}" class="text-decoration-none">
                    <div class="col-md-6 col-lg-12 mb-4">
                        <div class="trip-card p-4 shadow rounded bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- FROM -->
                                <div class="text-center">
                                    <span class="text-muted"><i class="fas fa-map-marker-alt text-danger"></i> From</span>
                                    <h3 class="text-primary">{{ date('H:i', strtotime($trip->start_time)) }}</h3>
                                    <span class="text-primary">{{ date(' d F Y', strtotime($trip->start_time)) }}</span>

                                    <br>
                                    <span class="text-ms">{{ $trip->startQuarter->district->region->name}},</span>
                                    <span class="text-ms">{{ $trip->startQuarter->district->name}},</span>
                                    <span class="fw-bold">{{ $trip->startQuarter->name }}</span>
                                </div>
    
                                <!-- DURATION -->
                                <div class="text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> {{ gmdate("H:i", strtotime($trip->end_time) - strtotime($trip->start_time)) }} 
                                        <br> 
                                        <i class="fas fa-route"></i>
                                    </small>
                                </div>
    
                                <!-- TO -->
                                <div class="text-center">
                                    <span class="text-muted"><i class="fas fa-map-marker-alt text-success"></i> To</span>
                                    <h3 class="text-primary">{{ date('H:i', strtotime($trip->end_time)) }}</h3>
                                    <span class="text-primary">{{ date('d F Y', strtotime($trip->start_time)) }}</span>
                                    <br>
                                    <span class="text-ms">{{ $trip->endQuarter->district->region->name}},</span>
                                    <span class="text-ms">{{ $trip->endQuarter->district->name}},</span>
                                    <span class="fw-bold">{{ $trip->endQuarter->name }}</span>
                                </div>
                            </div>
    
                            <hr>
    
                            <!-- Driver Info -->
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/50" class="rounded-circle me-2" alt="Driver">
                                <span class="fw-bold">Damian</span>
                                <span class="ms-auto"><i class="fas fa-star text-warning"></i> 5.0</span>
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
    
                            <div class="mt-3">
                                <span class="text-muted d-block mb-2">
                                    <i class="fas fa-shield-alt"></i> {{ __('No service fee.') }}
                                </span>
                            
                                @if($trip->parcels && count($trip->parcels))
                                    <hr>
                                    <div class="mb-2">
                                        <h6 class="text-muted"><i class="fas fa-box-open"></i> {{ __('Available Parcels') }}</h6>
                                    </div>
                            
                                    @foreach ($trip->parcels as $parcel)
                                        <div class="card mb-2 shadow-sm border-0">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $parcel->max_weight }} kg max</strong><br>
                                                        <small class="text-muted">1kg = {{ number_format($parcel->price_per_kg, 0, ',', ' ') }} UZS</small>
                                                    </div>
                                                    @if($parcel->max_weight > 0)
                                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('client.parcel.show', $parcel->id) }}">
                                                        <i class="fas fa-box"></i> {{ __('Book Parcel') }}
                                                    </a>
                                                    @else
                                                    <button class="btn btn-sm btn-outline-primary" disabled>
                                                        <i class="fas fa-box"></i> {{ __('Book Parcel') }}
                                                    </button>
                                                    @endif
                                                  
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
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
                            
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
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
        border-radius: 15px;
        padding: 15px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 15px;
    }
    .full-trip {
        opacity: 0.5;
    }
    .trip-card h5 {
        font-weight: bold;
    }
    .trip-card .price {
        font-size: 20px;
        font-weight: bold;
    }
    .no-service-fee {
        background-color: #222;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
    }
    .trip-card {
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .trip-card:hover {
        transform: translateY(-5px);
        box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.15);
    }
</style>
@endpush
