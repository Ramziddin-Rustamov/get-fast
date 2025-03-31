@extends('layouts.app')

@section('title', 'Edit Driver')

@section('content')
    <h1>Edit Driver</h1>

    <form action="{{ route('drivers.update', $driver->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Driver Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $driver->name }}" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ $driver->phone }}" required>
        </div>

        <hr>
        <h4>Vehicle Information</h4>

        <div class="mb-3">
            <label for="make" class="form-label">Make</label>
            <input type="text" class="form-control" id="make" name="make" value="{{ $driver->vehicle->make ?? '' }}" required>
        </div>

        <div class="mb-3">
            <label for="model" class="form-label">Model</label>
            <input type="text" class="form-control" id="model" name="model" value="{{ $driver->vehicle->model ?? '' }}" required>
        </div>

        <div class="mb-3">
            <label for="year" class="form-label">Year</label>
            <input type="number" class="form-control" id="year" name="year" value="{{ $driver->vehicle->year ?? '' }}" required>
        </div>

        <div class="mb-3">
            <label for="license_plate" class="form-label">License Plate</label>
            <input type="text" class="form-control" id="license_plate" name="license_plate" value="{{ $driver->vehicle->license_plate ?? '' }}" required>
        </div>

        <div class="mb-3">
            <label for="seats" class="form-label">Seats</label>
            <input type="number" class="form-control" id="seats" name="seats" value="{{ $driver->vehicle->seats ?? '' }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>

@endsection

@section('scripts')
<script>
  $(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function loadDistricts(region_id, selected_district = null) {
        if (region_id) {
            $.ajax({
                url: '/get-districts/' + region_id,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#district_id').empty().append('<option value="">Select District</option>');
                    $.each(data, function (key, value) {
                        $('#district_id').append('<option value="' + value.id + '"' + (selected_district == value.id ? ' selected' : '') + '>' + value.name + '</option>');
                    });
                },
                error: function (xhr) {
                    console.log('Xatolik:', xhr.responseText);
                }
            });
        } else {
            $('#district_id').empty().append('<option value="">Select District</option>');
        }
        $('#quarter_id').empty().append('<option value="">Select Quarter</option>');
    }

    function loadQuarters(district_id, selected_quarter = null) {
        if (district_id) {
            $.ajax({
                url: '/get-quarters/' + district_id,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#quarter_id').empty().append('<option value="">Select Quarter</option>');
                    $.each(data, function (key, value) {
                        $('#quarter_id').append('<option value="' + value.id + '"' + (selected_quarter == value.id ? ' selected' : '') + '>' + value.name + '</option>');
                    });
                },
                error: function (xhr) {
                    console.log('Xatolik:', xhr.responseText);
                }
            });
        } else {
            $('#quarter_id').empty().append('<option value="">Select Quarter</option>');
        }
    }

    let selectedRegion = "{{ $driver->region_id }}";
    let selectedDistrict = "{{ $driver->district_id }}";
    let selectedQuarter = "{{ $driver->quarter_id }}";

    if (selectedRegion) {
        loadDistricts(selectedRegion, selectedDistrict);
    }

    if (selectedDistrict) {
        loadQuarters(selectedDistrict, selectedQuarter);
    }

    $('#region_id').change(function () {
        loadDistricts($(this).val());
    });

    $('#district_id').change(function () {
        loadQuarters($(this).val());
    });

});
</script>
@endsection
