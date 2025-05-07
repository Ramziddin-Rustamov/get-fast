@extends('layouts.app')

@section('content')
    @can('client_web')
    <div class="container  border-primary bg-white ">
        <div class="d-flex justify-content-between align-items-center py-3 ">
            <a href="{{ route('client.trips.index') }}" class="btn btn-primary">{{ __('My Trips') }}</a>
            {{-- <a href="{{ route('trips.create') }}" class="btn btn-primary ">{{ __('Create Trip') }}</a> --}}
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
                <img class="rounded-circle mt-1" width="150px" src="{{ asset('image')}}/{{ $client->image }}">
                <h5 class="font-weight-bold">{{ $client->name }}</h5>
                <p class="text-black-50">{{ $client->phone }}</p>
                <div>
                    <a href="{{ route('profile.edit.client', $client->id) }}" class="btn btn-primary">{{ __('Edit') }}</a>
                </div>
            </div>
            <div class="col-md-9">
                <h4 class="mb-3 text-success ">{{ __('Profile Info') }}</h4>
                <div class="row g-3">
                    @foreach ([
                        'Name' => $client->name ?? 'there is no name',
                        'Phone Number' => $client->phone ?? 'there is no phone number',
                        'Region' => $client->region->name ?? 'there is no region address yet',
                        'District' => $client->district->name ?? 'there is no district address yet',
                        'Quarter' => $client->quarter->name ?? 'there is no quarter address yet',
                        'Home' => $client->home ?? 'there is no home address yet',
                        'Role' => $client->role,
                        'Created' => $client->created_at
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
