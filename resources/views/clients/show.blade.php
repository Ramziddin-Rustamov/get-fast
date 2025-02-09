@extends('layouts.app')

@section('title', 'Client Details')

@section('content')
    <h1>Client Details</h1>

    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <td>{{ $client->id }}</td>
        </tr>
        <tr>
            <th>Name</th>
            <td>{{ $client->name }}</td>
        </tr>
        <tr>
            <th>Phone</th>
            <td>{{ $client->phone }}</td>
        </tr>
    </table>

    <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-warning">Edit</a>
    <a href="{{ route('clients.index') }}" class="btn btn-secondary">Back to List</a>
@endsection
