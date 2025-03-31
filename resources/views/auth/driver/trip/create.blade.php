@extends('layouts.app')

@section('content')
<div class="container bg-white border-primary rounded-3 p-3">
    <div class="col-md-12">
        <div class="pb-2">
            <div class="d-flex justify-content-between align-items-between mb-3">
                <h5 class="text-right text-success">{{ __('Create Trip') }}</h5>

                <a href="{{ route('trips.index') }}" class="btn btn-primary">
                    {{ __('Back') }}
                </a>
            </div>
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
          @endif
        
            <form action="{{ route('trips.store') }}" method="POST">
                @csrf
                <div class="row mt-2">
                    <!-- Vehicle Selection -->
                    <div class="col-md-6">
                        <label class="labels mb-2 fs-8 fw-bold ">{{ __('Vehicle') }}</label>
                        <select class="form-control" name="vehicle_id" required>
                            @if ($driverVehicles->isEmpty())
                                <option value="">{{ __('You do not have any vehicle') }}</option>
                           @else
                           <option value="" disabled selected> {{ __('Select a vehicle') }}</option>
                            @foreach($driverVehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->make }} {{ $vehicle->model }} {{ $vehicle->year }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Locations Section -->
                    <div class="row mt-4">
                        <!-- Start Location -->
                        <div class="col-md-6">
                            <h5 class="mt-4 text-success fs-8 fw-bold" >{{ __('Start Location') }}</h5>
                            <div class=" mb-3">
                                <label class="col-md-4 col-form-label fs-8 fw-bold">{{ __('Region') }}</label>
                                <div class="col-md-8">
                                    <select name="start_region_id" class="form-control start-region" required>
                                        @foreach ($regions as $region)
                                            <option value="{{ $region->id }}" {{ old('start_region_id') == $region->id ? 'selected' : '' }}>
                                                {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                
                                <label class="col-md-4 col-form-label fs-8 fw-bold">{{ __('District') }}</label>
                                <div class="col-md-8">
                                    <select name="start_district_id" class="form-control start-district" required>
                                        <option value="">{{ __('Choose region first') }}</option>
                                        @if(old('start_district_id'))
                                            @foreach ($districts as $district)
                                                <option value="{{ $district->id }}" {{ old('start_district_id') == $district->id ? 'selected' : '' }}>
                                                    {{ $district->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                           
                            
                    
                                <label class="col-md-4 col-form-label fs-8 fw-bold">{{ __('Quarter') }}</label>
                                <div class="col-md-8">
                                    <select name="start_quarter_id" class="form-control start-quarter" required>
                                        <option value="">{{ __('Choose district first') }}</option>
                                        @if(old('start_quarter_id'))
                                            @foreach ($quarters as $quarter)
                                                <option value="{{ $quarter->id }}" {{ old('start_quarter_id') == $quarter->id ? 'selected' : '' }}>
                                                    {{ $quarter->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                        </div>   

                        <!-- End Location -->
                        <div class="col-md-6">
                            <h5 class="mt-4 text-success fs-8 fw-bold">{{ __('End Location') }}</h5>
                            <div class=" mb-3">
                                <label class="col-md-4 col-form-label fs-8 fw-bold">{{ __('Region') }}</label>
                                <div class="col-md-8">
                                    <select name="end_region_id" class="form-control end-region" required>
                                        @foreach ($regions as $region)
                                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class=" mb-3">
                                <label class="col-md-4 col-form-label fs-8 fw-bold">{{ __('District') }}</label>
                                <div class="col-md-8">
                                    <select name="end_district_id" class="form-control end-district" required>
                                        <option value="">{{ __('Choose region first') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class=" mb-3">
                                <label class="col-md-4 col-form-label fs-8 fw-bold">{{ __('Quarter') }}</label>
                                <div class="col-md-8">
                                    <select name="end_quarter_id" class="form-control end-quarter" required>
                                        <option value="">{{ __('Choose district first') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h5 class="mt-4 text-success fs-8 fw-bold">{{ __('Trip Details') }}</h5>
                    <div class="col-md-6 mb-2">
                        <label class="labels fs-8 fw-bold ">{{ __('Start Time') }}</label>
                        <input type="datetime-local" class="form-control" name="start_time" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="labels fs-8 fw-bold ">{{ __('End Time') }}</label>
                        <input type="datetime-local" class="form-control" name="end_time" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="labels fs-8 fw-bold">{{ __('Price per Seat in sum') }}</label>
                        <input type="number" step="1000" min="1000"  class="form-control" name="price_per_seat" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="labels fs-8 fw-bold">{{ __('Total Available Seats') }}</label>
                        <input type="number" class="form-control" name="available_seats" required>
                    </div>
                    
                    <!-- Pochta xizmati -->
                    <div class="col-md-12 mt-3">
                        <h5 class="mt-4 text-success">{{ __('Parcel Service') }}</h5>
                        <input type="checkbox" id="enable_parcel" name="enable_parcel">
                        <label for="enable_parcel">{{ __('Enable Parcel Service') }}</label>
                    </div>

                    <div id="parcel_fields" class="d-none">
                        <div class="col-md-6 mt-2">
                            <label class="labels fs-8 fw-bold">{{ __('Max Parcel Weight (kg)') }}</label>
                            <input type="number" step="0.1" class="form-control" name="max_weight">
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="labels fs-8 fw-bold">{{ __('Price per kg (sum)') }}</label>
                            <input type="number" step="0.01" class="form-control" name="price_per_kg">
                        </div>
                    </div>

                    <div class="col-md-12 mt-4">
                        <button class="btn btn-primary" type="submit">{{ __('Create Trip') }}</button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const appUrl = "{{ config('app.url') }}";

    const enableParcel = document.getElementById("enable_parcel");
        const parcelFields = document.getElementById("parcel_fields");

        // Boshlang‘ich holatda tekshiramiz
        if (enableParcel.checked) {
            parcelFields.classList.remove("d-none");
        }

        enableParcel.addEventListener("change", function () {
            if (this.checked) {
                parcelFields.classList.remove("d-none");
            } else {
                parcelFields.classList.add("d-none");
            }
        });
    
    function initializeLocationSelectors(prefix) {
        const regionSelector = $(`.${prefix}-region`);
        const districtSelector = $(`.${prefix}-district`);
        const quarterSelector = $(`.${prefix}-quarter`);

        // Region change handler
        regionSelector.on('change', function() {
            const regionId = $(this).val();
            districtSelector.html('<option value="">{{ __("Loading...") }}</option>');
            quarterSelector.html('<option value="">{{ __("Choose district first") }}</option>');

            if (regionId) {
                $.ajax({
                    url: `${appUrl}/api/v1/districts/region/${regionId}`,
                    type: 'GET',
                    success: function(data) {
                        districtSelector.empty().append('<option value="">{{ __("Select District") }}</option>');
                        // Check if data is nested under 'data' property
                        const districts = data.data ? data.data : data;
                        districts.forEach(district => {
                            districtSelector.append(`<option value="${district.id}">${district.name}</option>`);
                        });
                    }
                });
            }
        });

        // District change handler
        districtSelector.on('change', function() {
            const districtId = $(this).val();
            quarterSelector.html('<option value="">{{ __("Loading...") }}</option>');

            if (districtId) {
                $.ajax({
                    url: `${appUrl}/api/v1/quarters/districts/${districtId}`, // Fixed URL (added 's' in districts)
                    type: 'GET',
                    success: function(data) {
                        quarterSelector.empty().append('<option value="">{{ __("Select Quarter") }}</option>');
                        // Check if data is nested under 'data' property
                        const quarters = data.data ? data.data : data;
                        quarters.forEach(quarter => {
                            quarterSelector.append(`<option value="${quarter.id}">${quarter.name}</option>`);
                        });
                    },
                    error: function(xhr) {
                        console.error('Error loading quarters:', xhr.responseText);
                    }
                });
            }
        });

        // Initialize first region
        if (regionSelector.val()) {
            regionSelector.trigger('change');
        }
    }

    // Initialize both location selectors
    initializeLocationSelectors('start');
    initializeLocationSelectors('end');
});
</script>
@endsection