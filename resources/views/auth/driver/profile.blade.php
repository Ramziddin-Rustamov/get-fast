@extends('layouts.app')

@section('content')
    @can('driver')
        <h1>Driver Profile</h1>
    @endcan
@endsection