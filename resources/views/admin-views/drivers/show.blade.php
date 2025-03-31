@extends('layouts.app')

@section('title', 'Driver Details')

@section('content')
    <h1>Driver Details</h1>
    
    <p><strong>ID:</strong> {{ $driver->id }}</p>
    <p><strong>Name:</strong> {{ $driver->name }}</p>
    <p><strong>Phone:</strong> {{ $driver->phone }}</p>
    <p><strong>Region:</strong> {{ $driver->region->name ?? 'N/A' }}</p>
    <p><strong>District:</strong> {{ $driver->district->name ?? 'N/A' }}</p>
    <p><strong>Quarter:</strong> {{ $driver->quarter->name ?? 'N/A' }}</p>

    <hr>

    <h2>Vehicle Information</h2>
    @if($driver->vehicles)
        @foreach($driver->vehicles as $vehicle)
            <p><strong>Model:</strong> {{ $vehicle->model }}</p>
            <p><strong>Year:</strong> {{ $vehicle->year }}</p>
            <p><strong>Seats:</strong> {{ $vehicle->seats }}</p>
            <p><strong>License Plate:</strong> {{ $vehicle->license_plate }}</p>
            
            <hr>
        @endforeach
    @else
        <p>No vehicle assigned to this driver.</p>
    @endif

    <a href="{{ route('drivers.index') }}" class="btn btn-secondary">Back to List</a>
@endsection
