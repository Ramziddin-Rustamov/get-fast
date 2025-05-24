@extends('layouts.app')

@section('content')
    @can('client_web')
    <div class="container bg-white border rounded shadow-sm mt-4 p-4">
        <!-- Header buttons -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary m-0"><i class="fas fa-user"></i> {{ __('My Profile') }}</h3>
            <a href="{{ route('client.trips.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-route"></i> {{ __('My Trips') }}
            </a>
        </div>

        <!-- Alert messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <hr>

        <!-- Profile Section -->
        <div class="row">
            <!-- Left Side -->
            <div class="col-md-3 text-center border-end pe-4">
                <img src="{{ asset('image/' . $client->image) }}" class="rounded-circle img-thumbnail mb-3" width="150" alt="Profile Picture">
                <h5 class="fw-bold">{{ $client->name }}</h5>
                <p class="text-muted mb-3">{{ $client->phone }}</p>
                <a href="{{ route('profile.edit.client', $client->id) }}" class="btn btn-primary w-100">
                    <i class="fas fa-edit"></i> {{ __('Edit') }}
                </a>
            </div>

            <!-- Right Side -->
            <div class="col-md-9">
                <h5 class="text-success mb-4"><i class="fas fa-info-circle"></i> {{ __('Profile Info') }}</h5>
                <div class="row g-3">
                    @foreach ([
                        'Name' => $client->name ?? 'There is no name',
                        'Phone Number' => $client->phone ?? 'There is no phone number',
                        'Region' => $client->region->name ?? 'There is no region address yet',
                        'District' => $client->district->name ?? 'There is no district address yet',
                        'Quarter' => $client->quarter->name ?? 'There is no quarter address yet',
                        'Home' => $client->home ?? 'There is no home address yet',
                        'Role' => ucfirst($client->role),
                        'Created' => $client->created_at->format('d M Y, H:i'),
                    ] as $label => $value)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __($label) }}</label>
                            <input type="text" class="form-control bg-light" value="{{ $value }}" disabled>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <hr class="mt-4">
    </div>
    @endcan
@endsection
