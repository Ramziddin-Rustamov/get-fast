@extends('layouts.app')

@section('title', 'Edit Driver')

@section('content')
    <h1>Edit Driver</h1>
    <form action="{{ route('drivers.update', $driver->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Driver Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $driver->name }}" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ $driver->phone }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
@endsection
