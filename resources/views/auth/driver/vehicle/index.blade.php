@extends('layouts.app')

@section('content')

<div class="container border-primary bg-white rounded-3 p-3">
    <a href="{{route('home')}}" class="btn btn-outline-primary">{{ __('Home') }}</a>
    <div class="row">
        <div class="d-flex justify-content-between align-items-center ">
            <h4 class="text-success">{{ __('Vehicle Info') }} </h4>
            <a href="{{route('driver.create.vehicle.get')}}" class="btn btn-primary">{{ __('Add Vehicle') }}</a>
        </div>
        <p class="text-black-50"> {{__('You have')}} {{ $vehicles ? count($vehicles) : 0 }} {{ __('vehicles') }}</p>
        @if ($vehicles)
            @foreach ($vehicles as $vehicle)
                <div class="col-md-6 mb-3">
                    <h5 class="text-primary">{{ __('Vehicle') }} {{ $loop->iteration }}</h5>
                    <div class="row g-3">
                        @foreach ([
                            'Make' => $vehicle->make,
                            'Model' => $vehicle->model,
                            'Year' => $vehicle->year,
                            'License Plate' => $vehicle->license_plate,
                            'Seats' => $vehicle->seats,
                            'Created' => $vehicle->created_at
                        ] as $label => $value)
                            <div class="col-md-6">
                                <label class="labels">{{ __($label) }}</label>
                                <input type="text" class="form-control" value="{{ $value }}" disabled>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <a href="{{ route('driver.edit.vehicle.get', $vehicle->id) }}" class="btn btn-primary">{{ __('Edit') }}</a>
                        
                        <form action="{{ route('driver.delete.vehicle', $vehicle->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                        </form>
                    </div>
                    
                </div>
            @endforeach
        @else
            <h3 class="text-center">{{ __('You do not have a vehicle yet') }}</h3>
        @endif
    </div>
</div>
@endsection