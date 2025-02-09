@extends('layouts.app')

@section('title', 'Driver Details')

@section('content')
    <h1>Driver Details</h1>
    <p><strong>ID:</strong> {{ $driver->id }}</p>
    <p><strong>Name:</strong> {{ $driver->name }}</p>
    <p><strong>Phone:</strong> {{ $driver->phone }}</p>
    <a href="{{ route('drivers.index') }}" class="btn btn-secondary">Back to List</a>
@endsection
