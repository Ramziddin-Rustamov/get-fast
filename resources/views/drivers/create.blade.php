@extends('layouts.app')

@section('title', 'Add New Driver')

@section('content')
    <h1>Add New Driver</h1>
    <form action="{{ route('drivers.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Driver Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
        </div>
        <button type="submit" class="btn btn-success">Save</button>
    </form>
@endsection
