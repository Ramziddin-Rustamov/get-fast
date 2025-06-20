@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">

        @auth
        <div class="col-md-12">

            {{-- Admin --}}
            @can('admin')
                <div class="alert alert-info text-center shadow-sm">
                    <h4 class="mb-0">👑 {{ __('Admin Panel') }}</h4>
                </div>
            @endcan

            {{-- Driver --}}
            @can('driver_web')
            <div class="card shadow-lg border-0 mb-5">
                <div class="card-header bg-success text-white text-center py-4">
                    <h3 class="mb-0"><i class="fas fa-car me-2"></i>{{ __('Driver Dashboard') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 text-center">

                        <div class="col">
                            <a href="{{ route('profile.index.driver') }}" class="btn btn-outline-primary w-100 py-3 shadow-sm">
                                <i class="fas fa-user me-1"></i> {{ __('Profile') }}
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('driver.trips.index') }}" class="btn btn-outline-primary w-100 py-3 shadow-sm">
                                <i class="fas fa-road me-1"></i> {{ __('My Trips') }}
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('driver.get.vehicle') }}" class="btn btn-outline-primary w-100 py-3 shadow-sm">
                                <i class="fas fa-taxi me-1"></i> {{ __('My Vehicles') }}
                            </a>
                        </div>

                        {{-- <div class="col">
                            <a href="{{ route('profile.index.driver') }}" class="btn btn-outline-primary w-100 py-3 shadow-sm">
                                <i class="fas fa-wallet me-1"></i> {{ __('My Balance') }}
                            </a>
                        </div> --}}

                        {{-- <div class="col">
                            <a href="{{ route('profile.index.driver') }}" class="btn btn-outline-primary w-100 py-3 shadow-sm">
                                <i class="fas fa-star me-1"></i> {{ __('My Reviews') }}
                            </a>
                        </div> --}}

                    </div>
                </div>
            </div>
            @endcan
            {{-- End Driver --}}

            {{-- Client --}}
            @can('client_web')
            <div class="card shadow-lg border-0 mb-5">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0"><i class="fas fa-user me-2"></i>{{ __('Client Dashboard') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 text-center">

                        <div class="col">
                            <a href="{{ route('profile.index.client') }}" class="btn btn-outline-primary w-100 py-3 shadow-sm">
                                <i class="fas fa-user me-1"></i> {{ __('Profile') }}
                            </a>
                        </div>

                        <div class="col">
                            <a href="/" class="btn btn-outline-primary w-100 py-3 shadow-sm">
                                <i class="fas fa-road me-1"></i> {{ __('Book Trip') }}
                            </a>
                        </div>

                    </div>
                </div>
            </div>
            @endcan
            {{-- End Client --}}

        </div>
        @endauth

    </div>
</div>
@endsection
