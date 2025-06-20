@extends('layouts.app')

@section('content')
    @can('driver_web')
    <div class="container my-5">

        {{-- Action buttons --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('driver.trips.index') }}" class="btn btn-outline-primary shadow-sm">
                <i class="fas fa-road me-1"></i> {{ __('My Trips') }}
            </a>
            <a href="{{ route('driver.trips.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus me-1"></i> {{ __('Create Trip') }}
            </a>
        </div>

        {{-- Session Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <strong><i class="fas fa-check-circle me-1"></i></strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <strong><i class="fas fa-exclamation-triangle me-1"></i></strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Driver Profile Section --}}
        <div class="card shadow-lg border-0">
            <div class="row g-0">
                {{-- Left Sidebar --}}
                <div class="col-md-4 text-center border-end p-4">
                    <img src="{{ asset('image/' . $driver->image) }}" alt="Driver Image" class="img-fluid rounded-circle mb-3" width="150">
                    <h5 class="fw-bold">{{ $driver->name }}</h5>
                    <p class="text-muted mb-3"><i class="fas fa-phone-alt me-1"></i>{{ $driver->phone }}</p>
                    <a href="{{ route('profile.edit.driver', $driver->id) }}" class="btn btn-outline-success">
                        <i class="fas fa-edit me-1"></i> {{ __('Edit Profile') }}
                    </a>
                </div>

                {{-- Profile Info --}}
                <div class="col-md-8 p-4">
                    <h4 class="text-success mb-4">
                        <i class="fas fa-id-card me-2"></i>{{ __('Profile Info') }}
                    </h4>
                    <div class="row g-3">
                        @foreach ([
                            'Name' => $driver->name ?? 'N/A',
                            'Phone Number' => $driver->phone ?? 'N/A',
                            'Region' => $driver->region->name ?? 'N/A',
                            'District' => $driver->district->name ?? 'N/A',
                            'Quarter' => $driver->quarter->name ?? 'N/A',
                            'Home' => $driver->home ?? 'N/A',
                            'Role' => ucfirst($driver->role),
                            'Created' => $driver->created_at->format('F j, Y')
                        ] as $label => $value)
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __($label) }}</label>
                                <input type="text" class="form-control bg-light" value="{{ $value }}" disabled>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
    @endcan
@endsection
