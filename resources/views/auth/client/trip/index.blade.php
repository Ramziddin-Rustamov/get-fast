@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="pb-2">
        <div class="d-flex justify-content-between align-items-between mb-3">
            <h4 class="fw-bold text-success">{{ __('My Trips') }}</h4>
            {{-- <div>
                <a href="{{ route('driver.expired-trips.index') }}" class="btn btn-info">{{ __('My Expired Trips') }}</a>
                <a href="{{ route('driver.trips.create') }}" class="btn btn-primary">{{ __('Create Trip') }}</a>
                <a href="{{ route('profile.index.driver') }}" class="btn btn-primary">{{ __('Profile') }}</a>
            </div> --}}
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
       @if($trips)
       @foreach($trips as $trip)
       <div class="card shadow-sm border  border-primary mb-3">
           <div class="card  rounded-3 p-3">
               <div>
                   @if ($trip->status === 'active')
                   <div class="d-flex align-items-center">
                       <span class="fw-bold fs-6 text-success">{{ __('Active') }} <i class="fas fa-sync fa-spin"></i></span>
                       
                   </div>
               @elseif ($trip->status === 'cancelled')
                   <div class="d-flex align-items-center">
                       <span class="fw-bold fs-6 text-danger">{{ __('Cancelled') }} <i class="fas fa-cog fa-spin"></i></span>
                   </div>  
               @elseif ($trip->status === 'full')
                   <div class="d-flex align-items-center"></div>
                       <span class="fw-bold fs-6 text-danger">{{ __('Full') }} <i class="fas fa-circle-notch fa-spin"></i></span>
                   </div>
               @endif
               </div>
               <div class="d-flex justify-content-between align-items-center">
                   <div class="d-flex align-items-center">
                       <span class="fw-bold fs-5">{{ \Carbon\Carbon::parse($trip->start_time)->format('H:i') }}</span>
                       </div> 
                   <span class="fas fa-arrow-right"></span>

                   <div class="d-flex align-items-center">
                       <span class=" mx-2">
                           <span class="fw-bold fs-5">
                               {{ \Carbon\Carbon::parse($trip->start_time)->diff(\Carbon\Carbon::parse($trip->end_time))->h }} h
                               {{ \Carbon\Carbon::parse($trip->start_time)->diff(\Carbon\Carbon::parse($trip->end_time))->i }} m
                           </span>                            
                       </span>
                   </div> 

                   <span class="fas fa-arrow-right"></span>
                   <div class="d-flex align-items-center">
                       <span class="fw-bold fs-5 ">{{ \Carbon\Carbon::parse($trip->end_time)->format('H:i') }}</span>
                   </div> 

                   
                   </div>
                   <hr>
               <spam class="text-bold fw-bold fs-5"> {{ __('From') }} <i class="fas fa-map-marker-alt"></i></spam>
               
               <div class="d-flex justify-content-between text-success">
                   <strong> {{$trip->startQuarter->district->region->name}} <i class="fas fa-arrow-right"></i> {{ $trip->startQuarter->district->name }} <i class="fas fa-arrow-right"></i> {{ $trip->startQuarter->name }}</strong>
               </div>
               <hr>
               <label class="text-bold fw-bold fs-5"> {{ __('To') }} <i class="fas fa-map-marker-alt"></i></label>
               <div class="d-flex justify-content-between text-success">

                   <strong>{{$trip->endQuarter->district->region->name}} <i class="fas fa-arrow-right"></i> {{ $trip->endQuarter->district->name }} <i class="fas fa-arrow-right"></i> {{ $trip->endQuarter->name }}</strong>
               </div>
               <div>
                   <hr>
                   <div class="d-flex justify-content-between">
                       <strong>{{ __('Price per seat:') }} </strong>
                       <strong class="fs-6 fw-bold text-dark underline">{{ number_format($trip->price_per_seat) }} <span>So'm </span></strong> 
                   </div>
                   <div class="d-flex justify-content-between">
                       <strong>{{ __('Available Seats:') }} </strong>
                       <strong class="fs-6 text-success">{{ $trip->available_seats }}</strong>
                   </div>
                   <div class="d-flex justify-content-between ">
                       <strong >{{ __('Total Seats:') }} </strong>
                       <strong class="fs-6 text-danger">{{ $trip->total_seats }}</strong>
                   </div>
               </div>
               @if($trip->parcels)
                   <hr>
                   <div class="d-flex justify-content-between">
                       <div>
                           <label class="text-bold fw-bold fs-5 mb-1"> {{ __('Parcels') }} <i class="fas fa-box text-success"></i></label> 
                       </div>
                       <div class="text-align-center">
                           <span class=" fs-10 mb-1"> {{ __('50x50 sm') }}</span> 
                       </div>
                   </div>
                   @foreach($trip->parcels as $pa)
                   <div class="d-flex justify-content-between">
                       <p class="mb-1">
                           📦 <strong>{{ __('Max Weight:') }}</strong> <br>
                           💰 <strong>{{ __('Price per kg:') }}</strong> 
                       </p>
                       <p class="mb-1">
                            <strong> <i class="fas fa-arrow-right"></i> </strong> <br>
                            <strong> <i class="fas fa-arrow-right"></i> </strong> 
                       </p>
                       <p>
                           📦 <strong>{{ $pa->max_weight }} kg</strong> <br>
                           💰 <strong>{{ number_format($pa->price_per_kg) }} So'm</strong>
                       </p>
                   </div>

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
       @else
            <p class="text-center text-muted">{{__('You have no trips yet')}}</p>
       @endif
    </div>
</div>
@endsection
