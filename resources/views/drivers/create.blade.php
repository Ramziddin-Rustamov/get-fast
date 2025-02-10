@extends('layouts.app')

@section('title', 'Add New Driver')

@section('content')
    <h1>Add New Driver</h1>

    <form action="{{ route('drivers.store') }}" method="POST">
        @csrf
    
        <div class="mb-3">
            <label for="name" class="form-label">Driver Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
    
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
        </div>
    
        <div class="mb-3">
            <label for="region_id" class="form-label">Region</label>
            <select class="form-control" id="region_id" name="region_id" required>
                <option value="">Select Region</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                @endforeach
            </select>
        </div>
    
        <div class="mb-3">
            <label for="district_id" class="form-label">District</label>
            <select class="form-control" id="district_id" name="district_id" required>
                <option value="">Select District</option>
            </select>
        </div>
    
        <div class="mb-3">
            <label for="quarter_id" class="form-label">Quarter</label>
            <select class="form-control" id="quarter_id" name="quarter_id" required>
                <option value="">Select Quarter</option>
            </select>
        </div>
    
        <hr>
        <h4>Vehicle Information</h4>
    
        <div class="mb-3">
            <label for="make" class="form-label">Make</label>
            <input type="text" class="form-control" id="make" name="make" required>
        </div>
    
        <div class="mb-3">
            <label for="model" class="form-label">Model</label>
            <input type="text" class="form-control" id="model" name="model" required>
        </div>
    
        <div class="mb-3">
            <label for="year" class="form-label">Year</label>
            <input type="number" class="form-control" id="year" name="year" required>
        </div>
    
        <div class="mb-3">
            <label for="license_plate" class="form-label">License Plate</label>
            <input type="text" class="form-control" id="license_plate" name="license_plate" required>
        </div>
    
        <div class="mb-3">
            <label for="seats" class="form-label">Seats</label>
            <input type="number" class="form-control" id="seats" name="seats" required>
        </div>
    
        <button type="submit" class="btn btn-success">Save</button>
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

    $('#region_id').change(function () {
        var region_id = $(this).val();

        if (region_id) {
            $.ajax({
                url: '/get-districts/' + region_id,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#district_id').empty().append('<option value="">Select District</option>');
                    $.each(data, function (key, value) {
                        $('#district_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                },
                error: function (xhr, status, error) {
                    console.log('Xatolik:', xhr.responseText);
                }
            });
        } else {
            $('#district_id').empty().append('<option value="">Select District</option>');
        }
        $('#quarter_id').empty().append('<option value="">Select Quarter</option>');
    });

    $('#district_id').change(function () {
        var district_id = $(this).val();

        if (district_id) {
            $.ajax({
                url: '/get-quarters/' + district_id,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#quarter_id').empty().append('<option value="">Select Quarter</option>');
                    $.each(data, function (key, value) {
                        $('#quarter_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                },
                error: function (xhr, status, error) {
                    console.log('Xatolik:', xhr.responseText);
                }
            });
        } else {
            $('#quarter_id').empty().append('<option value="">Select Quarter</option>');
        }
    });
});

</script>
@endsection
