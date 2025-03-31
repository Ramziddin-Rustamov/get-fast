@extends('layouts.app')

@section('title', 'Admins')

@section('content')
    <h1>Admins List</h1>
    <a href="{{ route('admins.create') }}" class="btn btn-primary">Add New Admin</a>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($admins as $admin)
                <tr>
                    <td>{{ $admin->id }}</td>
                    <td>{{ $admin->name }}</td>
                    <td>{{ $admin->phone }}</td>
                    <td>
                        <a href="{{ route('admins.show', $admin->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('admins.edit', $admin->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        {{-- <form action="{{ route('admins.destroy', $admin->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form> --}}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
