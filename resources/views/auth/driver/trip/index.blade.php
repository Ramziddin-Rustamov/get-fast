@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="pb-2">
        <div class="d-flex justify-content-between align-items-between mb-3">
            <h4 class="fw-bold text-success">{{ __('My Trips') }}</h4>
            <div>
                <a href="{{ route('trips.create') }}" class="btn btn-primary">{{ __('Create Trip') }}</a>
                <a href="{{ route('profile.index.driver') }}" class="btn btn-primary">{{ __('Profile') }}</a>
            </div>
        </div>
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

    <div class="list-group">
        @foreach($trips as $trip)
            <div class="card shadow-sm border  border-primary mb-3">
                <div class="card  rounded-3 p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold fs-5">{{ \Carbon\Carbon::parse($trip->start_time)->format('H:i') }}</span>
                            <span class="text-muted mx-2">
                                <span class="fas fa-arrow-right"></span>
                                <span>
                                    {{ \Carbon\Carbon::parse($trip->start_time)->diff(\Carbon\Carbon::parse($trip->end_time))->h }} h
                                    {{ \Carbon\Carbon::parse($trip->start_time)->diff(\Carbon\Carbon::parse($trip->end_time))->i }} m
                                </span>                                <span class="fas fa-arrow-right"></span>
                            </span>
                            <span class="fw-bold fs-5">{{ \Carbon\Carbon::parse($trip->end_time)->format('H:i') }}</span>
                        </div>
                        </div>
                    
                    <spam class="text-bold"> {{ __('From') }} <i class="fas fa-map-marker-alt"></i></spam>
                    
                    <div class="d-flex justify-content-between">
                        <strong>{{$trip->startQuarter->district->region->name}}</strong>
                        <strong class="fs-6">{{ $trip->startQuarter->district->name }}</strong>
                        <strong class="fs-6">{{ $trip->startQuarter->name }}</strong>
                    </div>
                    <hr>
                    <label class="text-bold"> {{ __('To') }} <i class="fas fa-map-marker-alt"></i></label>
                    <div class="d-flex justify-content-between">
                        <strong>{{$trip->endQuarter->district->region->name}}</strong>
                        <strong class="fs-6">{{ $trip->endQuarter->district->name }}</strong>
                        <strong class="fs-6">{{ $trip->endQuarter->name }}</strong>
                    </div>
                    @if($trip->parcels)
                        <hr>
                        <h6 class="fw-bold">{{ __('Parcels') }}</h6>
                        @foreach($trip->parcels as $pa)
                            <p class="mb-1">
                                📦 <strong>{{ __('Max Weight:') }}</strong> {{ $pa->max_weight }} kg | <br>
                                💰 <strong>{{ __('Price per kg:') }}</strong> {{ number_format($pa->price_per_kg, 2) }} So'm
                            </p>
                        @endforeach
                    @endif
                    <div class="d-flex align-items-center">
                        <img src="{{ asset('image')}}/{{ $trip->driver->image }}" class="rounded-circle me-2" width="40" height="40">
                        <strong>{{ $trip->driver->name }}</strong>
                        <span class="ms-auto">⭐ 4.9</span>
                    </div>
                </div>
                
            </div>
        @endforeach
    </div>
</div>
@endsection
