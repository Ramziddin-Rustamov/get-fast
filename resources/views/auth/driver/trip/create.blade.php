@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="bg-white shadow-sm rounded-4 p-4 border border-success-subtle">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-success fw-bold">{{ __('Create Trip') }}</h3>
            <a href="{{ route('driver.trips.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> {{ __('Back') }}
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('driver.trips.store') }}" method="POST">
            @csrf

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Vehicle') }}</label>
                    <select class="form-select" name="vehicle_id" required>
                        @if ($driverVehicles->isEmpty())
                            <option value="">{{ __('You do not have any vehicle') }}</option>
                        @else
                            <option value="" disabled selected>{{ __('Select a vehicle') }}</option>
                            @foreach($driverVehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="row g-4">
                <!-- Start Location -->
                <div class="col-md-6">
                    <h5 class="text-success fw-bold">{{ __('Start Location') }}</h5>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Region') }}</label>
                        <select name="start_region_id" class="form-select start-region" required>
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
                            @if(old('start_district_id'))
                                @foreach ($districts as $district)
                                    <option value="{{ $district->id }}" {{ old('start_district_id') == $district->id ? 'selected' : '' }}>
                                        {{ $district->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Quarter') }}</label>
                        <select name="start_quarter_id" class="form-select start-quarter" required>
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
                    <h5 class="text-success fw-bold">{{ __('End Location') }}</h5>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Region') }}</label>
                        <select name="end_region_id" class="form-select end-region" required>
                            @foreach ($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->name }}</option>
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
            </div>

            <h5 class="mt-4 text-success fw-bold">{{ __('Trip Details') }}</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Start Time') }}</label>
                    <input type="datetime-local" name="start_time" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('End Time') }}</label>
                    <input type="datetime-local" name="end_time" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Price per Seat in sum') }}</label>
                    <input type="number" step="1000" min="1000" name="price_per_seat" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Total Available Seats') }}</label>
                    <input type="number" name="available_seats" class="form-control" required>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="text-success fw-bold">{{ __('Parcel Service') }}</h5>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="enable_parcel" name="enable_parcel">
                <label class="form-check-label" for="enable_parcel">{{ __('Enable Parcel Service') }}</label>
            </div>

            <div id="parcel_fields" class="row g-3 d-none">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Max Parcel Weight (kg)') }}</label>
                    <input type="number" step="0.1" name="max_weight" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Price per kg (sum)') }}</label>
                    <input type="number" step="0.01" name="price_per_kg" class="form-control">
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                    <i class="bi bi-plus-circle"></i> {{ __('Create Trip') }}
                </button>
            </div>
        </form>
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