@extends('layouts.app')

@section('content')
    @can('client')
    <h1>Client Profile</h1>
    <p>Name: {{ $client->name }}</p>
    <p>Phone: {{ $client->phone }}</p>
    <p>Email: {{ $client->email }}</p>
    <p>Password: {{ $client->password }}</p>
    <p>Created At: {{ $client->created_at }}</p>
    <p>Updated At: {{ $client->updated_at }}</p>
    <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-warning">Edit</a>
    <a href="{{ route('clients.index') }}" class="btn btn-secondary">Back to List</a>
    @endcan
@endsection