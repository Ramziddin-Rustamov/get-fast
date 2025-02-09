@extends('layouts.app')

@section('title', 'Admin Details')

@section('content')
    <h1>Admin Details</h1>

    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <td>{{ $admin->id }}</td>
        </tr>
        <tr>
            <th>Name</th>
            <td>{{ $admin->name }}</td>
        </tr>
        <tr>
            <th>Phone</th>
            <td>{{ $admin->phone }}</td>
        </tr>
    </table>

    <a href="{{ route('admins.edit', $admin->id) }}" class="btn btn-warning">Edit</a>
    <a href="{{ route('admins.index') }}" class="btn btn-secondary">Back to List</a>
@endsection
