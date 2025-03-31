@extends('layouts.app')

@section('content')
    @can('driver_web')
    <div class="container  border-primary bg-white ">
        <div class="d-flex justify-content-between align-items-center py-3 ">
            <a href="{{ route('trips.index') }}" class="btn btn-primary">{{ __('Trips') }}</a>
            <a href="{{ route('trips.create') }}" class="btn btn-primary ">{{ __('Create Trip') }}</a>
        </div>
        <hr> 
        <div class="row ">
            <div class="col-md-3 border-right text-center pb-5">
                <img class="rounded-circle mt-1" width="150px" src="https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg">
                <h5 class="font-weight-bold">{{ $driver->name }}</h5>
                <p class="text-black-50">{{ $driver->phone }}</p>
            </div>
            <div class="col-md-9">
                <h4 class="mb-3 text-success ">{{ __('Profile Info') }}</h4>
                <div class="row g-3">
                    @foreach ([
                        'Name' => $driver->name,
                        'Phone Number' => $driver->phone,
                        'Region' => $driver->region->name,
                        'District' => $driver->district->name,
                        'Quarter' => $driver->quarter->name,
                        'Home' => $driver->home,
                        'Role' => $driver->role,
                        'Created' => $driver->created_at
                    ] as $label => $value)
                        <div class="col-md-6">
                            <label class="labels">{{ __($label) }}</label>
                            <input type="text" class="form-control" value="{{ $value }}" disabled>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="d-flex justify-content-between align-items-center ">
                <h4 class="text-success">{{ __('Vehicle Info') }} </h4>
                <a href="#" class="btn btn-primary">{{ __('Add Vehicle') }}</a>
            </div>
            <p class="text-black-50"> {{__('You have')}} {{ $driver->vehicles ? count($driver->vehicles) : 0 }} {{ __('vehicles') }}</p>
            @if ($driver->vehicles)
                @foreach ($driver->vehicles as $vehicle)
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
                        <div class="mt-3">
                            <a href="#" class="btn btn-primary">{{ __('Edit') }}</a>
                            <a href="#" class="btn btn-danger">{{ __('Delete') }}</a>
                        </div>
                    </div>
                @endforeach
            @else
                <h3 class="text-center">{{ __('You do not have a vehicle yet') }}</h3>
            @endif
        </div>
    </div>
    @endcan
@endsection
