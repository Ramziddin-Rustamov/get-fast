@extends('layouts.app')

@section('title', 'Drivers List')

@section('content')
    <h1>Drivers</h1>
    <a href="{{ route('drivers.create') }}" class="btn btn-primary mb-3">Add New Driver</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($drivers as $driver)
                <tr>
                    <td>{{ $driver->id }}</td>
                    <td>{{ $driver->name }}</td>
                    <td>{{ $driver->phone }}</td>
                    <td>
                        <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-info">View</a>
                        <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('drivers.destroy', $driver->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
