@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        @auth
        <div class="col-md-12">
            <div class="card">

                <div class="card-body">
                 
                        @can('admin')
                            <h4 class="text-center">{{ __('Admin') }}</h4>
                        @endcan
                        {{-- Driver --}}
                        @can('driver_web')
                            <div class="container my-5">
                                <div class="card shadow-lg border-0">
                                    <div class="card-header bg-success text-white text-center py-3">
                                        <h3 class="mb-0"><i class="fas fa-car"></i> {{ __('Driver Panel') }}</h3>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
                                            <div class="col">
                                                <a href="{{ route('profile.index.driver') }}" class="btn btn-outline-primary w-100 py-3">
                                                    <i class="fas fa-user"></i> {{ __('Profile') }}
                                                </a>
                                            </div>
                                            <div class="col">
                                                <a href="{{ route('driver.trips.index') }}" class="btn btn-outline-primary w-100 py-3">
                                                    <i class="fas fa-road"></i> {{ __('Trips') }}
                                                </a>
                                            </div>
                                            <div class="col">
                                                {{-- <a href="{{ route('expired-trips.index') }}" class="btn btn-outline-primary w-100 py-3">
                                                    <i class="fas fa-route"></i> {{ __('Expired Trips') }}
                                                </a> --}}
                                            </div>
                                            <div class="col">
                                                <a href="{{ route('driver.get.vehicle') }}" class="btn btn-outline-primary w-100 py-3">
                                                    <i class="fas fa-taxi"></i> {{ __('My Vehicles') }}
                                                </a>
                                            </div>

                                            <div class="col">
                                                <a href="{{ route('profile.index.driver') }}" class="btn btn-outline-primary w-100 py-3">
                                                    <i class="fas fa-wallet"></i> {{ __('My Balance') }}
                                                </a>
                                            </div>

                                            <div class="col">
                                                <a href="{{ route('profile.index.driver') }}" class="btn btn-outline-primary w-100 py-3">
                                                    <i class="fas fa-star"></i> {{ __('My Reviews') }}
                                                </a>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endcan
                        {{-- End Driver --}}
                        
                        {{-- Client --}}
                        @can('client_web')
                        <div class="container my-5">
                            <div class="card shadow-lg border-0">
                                <div class="card-header bg-success text-white text-center py-3">
                                    <h3 class="mb-0"><i class="fas fa-user"></i> {{ __('Client Panel') }}</h3>
                                </div>
                                <div class="card-body text-center">
                                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
                                        <div class="col">
                                            <a href="{{ route('profile.index.client') }}" class="btn btn-outline-primary w-100 py-3">
                                                <i class="fas fa-user"></i> {{ __('Profile') }}
                                            </a>
                                        </div>
                                        <div class="col">
                                            <a href="/" class="btn btn-outline-primary w-100 py-3">
                                                <i class="fas fa-road"></i> {{ __('Book Trip') }}
                                            </a>
                                        </div>


                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan
                        {{-- End Client --}}
                </div>
            </div>
        </div>
        @endauth
    </div>
</div>
@endsection
