@extends('layouts.app')

@section('content')

<div class="container py-4 mt-4" >

    <h2>Edit Vehicle</h2>

    <form action="{{ route('vehicles.update', $vehicle->id) }}"
          method="POST">

        @csrf
        @method('PUT')

        <div class="mb-3">

            <label>Driver</label>
        
            <select name="user_id"
                    class="form-control">
        
                <option value="">
                    Select Driver
                </option>
        
                @foreach($drivers as $driver)
        
                    <option value="{{ $driver->id }}"
                        {{ isset($vehicle) && $vehicle->user_id == $driver->id ? 'selected' : '' }}>
        
                        {{ $driver->first_name }}
                        {{ $driver->last_name }}
                        -
                        {{ $driver->phone }}
        
                    </option>
        
                @endforeach
        
            </select>
        
        </div>

        <div class="mb-3">
            <label>Color ID</label>
            <input type="number"
                   name="color_id"
                   value="{{ $vehicle->color_id }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Model</label>
            <input type="text"
                   name="model"
                   value="{{ $vehicle->model }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Car Number</label>
            <input type="text"
                   name="car_number"
                   value="{{ $vehicle->car_number }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Tech Passport Number</label>
            <input type="text"
                   name="tech_passport_number"
                   value="{{ $vehicle->tech_passport_number }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Seats</label>
            <input type="number"
                   name="seats"
                   value="{{ $vehicle->seats }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Status</label>

            <select name="status"
                    class="form-control">

                <option value="1"
                    {{ $vehicle->status == 1 ? 'selected' : '' }}>
                    Active
                </option>

                <option value="0"
                    {{ $vehicle->status == 0 ? 'selected' : '' }}>
                    Inactive
                </option>

            </select>
        </div>

        <button class="btn btn-primary">
            Update
        </button>

    </form>

</div>

@endsection