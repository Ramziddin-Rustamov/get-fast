@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        @auth
        <div class="col-md-12">
            <div class="card">

                <div class="card-body">
                 
                        @can('admin')
                            <h4 class="text-center">{{ __('Admin') }}</h4>
                        @endcan

                        @can('driver_web')
                        <h4 class="text-success">{{ __('Driver') }}</h4>
                            <a href="{{ route('profile.index.driver') }}" class="btn btn-primary">Ma'lumotlarim</a>
                        @endcan

                        @can('client_web')
                            <a href="{{ route('profile.index.client') }}" class="btn btn-primary">Ma'lumotlarim</a>
                        @endcan
                </div>
            </div>
        </div>
        @endauth
    </div>
</div>
@endsection
