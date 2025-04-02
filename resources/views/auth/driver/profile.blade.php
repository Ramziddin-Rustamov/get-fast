@extends('layouts.app')

@section('content')
    @can('driver_web')
    <div class="container  border-primary bg-white ">
        <div class="d-flex justify-content-between align-items-center py-3 ">
            <a href="{{ route('trips.index') }}" class="btn btn-primary">{{ __('Trips') }}</a>
            <a href="{{ route('trips.create') }}" class="btn btn-primary ">{{ __('Create Trip') }}</a>
        </div>
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
        <hr> 
        <div class="row ">
            <div class="col-md-3 border-right text-center pb-5">
                <img class="rounded-circle mt-1" width="150px" src="https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg">
                <h5 class="font-weight-bold">{{ $driver->name }}</h5>
                <p class="text-black-50">{{ $driver->phone }}</p>
                <div>
                    <a href="{{ route('profile.edit.driver', $driver->id) }}" class="btn btn-primary">{{ __('Edit') }}</a>
                </div>
            </div>
            <div class="col-md-9">
                <h4 class="mb-3 text-success ">{{ __('Profile Info') }}</h4>
                <div class="row g-3">
                    @foreach ([
                        'Name' => $driver->name ?? 'there is no name',
                        'Phone Number' => $driver->phone ?? 'there is no phone number',
                        'Region' => $driver->region->name ?? 'there is no region address yet',
                        'District' => $driver->district->name ?? 'there is no district address yet',
                        'Quarter' => $driver->quarter->name ?? 'there is no quarter address yet',
                        'Home' => $driver->home ?? 'there is no home address yet',
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
       
    </div>
    @endcan
@endsection
