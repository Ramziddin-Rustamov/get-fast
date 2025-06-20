@extends('layouts.app')

@section('content')

@guest
<div class="vh-100 d-flex justify-content-center align-items-center bg-gradient" style="background: linear-gradient(135deg, #f0f4f8, #d9e4f5);">
    <div class="text-center bg-white rounded-4 shadow-lg p-5" style="min-width: 320px; max-width: 500px;">
        <h1 class="mb-4 text-primary fw-bold">{{__('Hello, welcome to Qadam')}}</h1>
        <h4 class="mb-4 text-secondary">{{__('Select your role')}}</h4>

        <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
            <a href="{{ route('client.auth.register.index') }}" class="btn btn-outline-primary btn-lg rounded-pill px-4 py-2 transition">
                <i class="fas fa-user me-2"></i>{{__(' Register as Client')}}
            </a>
            <a href="{{ route('driver.auth.register.index') }}" class="btn btn-outline-success btn-lg rounded-pill px-4 py-2 transition">
                <i class="fas fa-car me-2"></i>{{__(' Register as Driver')}}
            </a>
            <a href="{{ route('auth.login.index') }}" class="btn btn-outline-warning btn-lg rounded-pill px-4 py-2 transition">
                <i class="fas fa-sign-in-alt me-2"></i> {{__('Login')}}
            </a>
        </div>
    </div>
</div>

<style>
    .transition {
        transition: all 0.3s ease-in-out;
    }
    .transition:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
</style>
@endguest


@auth
    <div class="container mt-4">
    

            @if(isset($departureTrips) || isset($returnTrips))
            {{-- Agar qidiruvdan kelgan bo‘lsa --}}
            
            @if($departureTrips->isNotEmpty())
                <h4>Borish uchun yo‘nalishlar:</h4>
                <div class="row">
                @foreach($departureTrips as $trip)
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
                            @if (isset($trip->driver->image))
                                <img src="{{ asset('image')}}/{{ $trip->driver->image }}" class="rounded-circle me-3" width="50" height="50" alt="Driver">
                           @else
                                <img src="{{ asset('image/avatar.png') }}" class="rounded-circle me-3" width="50" height="50" alt="Driver">
                                @endif                                

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
                <div class="text-center mt-4">
                    <h5 class="text-muted">Borish uchun mos safar topilmadi.</h5>
                </div>
            @endif

            @if($returnTrips->isNotEmpty())
                <h4>Qaytish uchun yo‘nalishlar:</h4>
                <div class="row">
                @foreach($returnTrips as $trip)
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
                            @if (isset($trip->driver->image))
                                <img src="{{ asset('image')}}/{{ $trip->driver->image }}" class="rounded-circle me-3" width="50" height="50" alt="Driver">
                           @else
                                <img src="{{ asset('image/avatar.png') }}" class="rounded-circle me-3" width="50" height="50" alt="Driver">
                                @endif                                

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
            @endif

        @elseif(isset($trips) && $trips->count() > 0)
            {{-- Search bo‘lmagan, lekin default tripslar bor --}}
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold"> Today {{ date('d F Y') }} </h4>
                <button class="btn btn-primary px-4 my-2" data-bs-toggle="modal" data-bs-target="#searchModal">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>

            <div class="row">
                @foreach($trips as $trip)
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
                            @if (isset($trip->driver->image))
                                <img src="{{ asset('image')}}/{{ $trip->driver->image }}" class="rounded-circle me-3" width="50" height="50" alt="Driver">
                           @else
                                <img src="{{ asset('image/avatar.png') }}" class="rounded-circle me-3" width="50" height="50" alt="Driver">
                                @endif                                

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
            {{-- Hech narsa topilmasa --}}
            <div class="text-center mt-5">
                <h3 class="text-muted">No active trips available.</h3>
            </div>
        @endif


    <!-- Search Modal -->
   <!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('welcome.trips.search') }}" method="GET" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="searchModalLabel">Search for a Trip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row g-4">
                    <!-- Start Location -->
                    <div class="col-md-6">
                        <h5 class="text-success fw-bold">{{ __('Start Location') }}</h5>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Region') }}</label>
                            <select name="start_region_id" class="form-select start-region" required>
                                <option value="">{{ __('Select Region') }}</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}" {{ old('start_region_id') == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('District') }}</label>
                            <select name="start_district_id" class="form-select start-district" required>
                                <option value="">{{ __('Choose region first') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Quarter') }}</label>
                            <select name="start_quarter_id" class="form-select start-quarter" required>
                                <option value="">{{ __('Choose district first') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- End Location -->
                    <div class="col-md-6">
                        <h5 class="text-success fw-bold">{{ __('End Location') }}</h5>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Region') }}</label>
                            <select name="end_region_id" class="form-select end-region" required>
                                <option value="">{{ __('Select Region') }}</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}" {{ old('end_region_id') == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('District') }}</label>
                            <select name="end_district_id" class="form-select end-district" required>
                                <option value="">{{ __('Choose region first') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Quarter') }}</label>
                            <select name="end_quarter_id" class="form-select end-quarter" required>
                                <option value="">{{ __('Choose district first') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">

                        <div class="mb-3">
                            <label class="form-label">{{ __('Trip Date') }}</label>
                                <input type="date" name="trip_date" id="trip_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="return_trip" id="return_trip_checkbox">
                                <label class="form-check-label" for="return_trip_checkbox">
                                    Qaytish safari (Return trip)
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3 d-none" id="return_date_wrapper">
                            <label for="return_date" class="form-label">Qaytish sanasi:</label>
                            <input type="date" name="return_date" id="return_date" class="form-control">
                        </div>
                        

                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-success w-100">Search</button>
            </div>
        </form>
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

@section('scripts')
@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {

    const checkbox = document.getElementById('return_trip_checkbox');
        const returnDateWrapper = document.getElementById('return_date_wrapper');

        checkbox.addEventListener('change', function () {
            if (checkbox.checked) {
                returnDateWrapper.classList.remove('d-none');
            } else {
                returnDateWrapper.classList.add('d-none');
                document.getElementById('return_date').value = ''; // maydonni tozalash
            }
        });

    const appUrl = "{{ config('app.url') }}";

    function initializeLocationSelectors(prefix) {
        const regionSelector = $(`.${prefix}-region`);
        const districtSelector = $(`.${prefix}-district`);
        const quarterSelector = $(`.${prefix}-quarter`);

        // Region -> District
        regionSelector.on('change', function() {
            const regionId = $(this).val();
            districtSelector.html('<option value="">Loading...</option>');
            quarterSelector.html('<option value="">Choose district first</option>');

            if (regionId) {
                $.ajax({
                    url: `${appUrl}/api/v1/districts/region/${regionId}`,
                    type: 'GET',
                    success: function(data) {
                        const districts = data.data ?? data;
                        districtSelector.html('<option value="">Select District</option>');
                        districts.forEach(d => {
                            districtSelector.append(`<option value="${d.id}">${d.name}</option>`);
                        });
                    }
                });
            }
        });

        // District -> Quarter
        districtSelector.on('change', function() {
            const districtId = $(this).val();
            quarterSelector.html('<option value="">Loading...</option>');

            if (districtId) {
                $.ajax({
                    url: `${appUrl}/api/v1/quarters/districts/${districtId}`,
                    type: 'GET',
                    success: function(data) {
                        const quarters = data.data ?? data;
                        quarterSelector.html('<option value="">Select Quarter</option>');
                        quarters.forEach(q => {
                            quarterSelector.append(`<option value="${q.id}">${q.name}</option>`);
                        });
                    }
                });
            }
        });

        // Auto trigger if pre-selected
        if (regionSelector.val()) regionSelector.trigger('change');
        if (districtSelector.val()) districtSelector.trigger('change');
    }

    initializeLocationSelectors('start');
    initializeLocationSelectors('end');
});

</script>
@endsection

@endsection
