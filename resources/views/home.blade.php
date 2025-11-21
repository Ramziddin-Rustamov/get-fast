@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">

        @auth
        <div class="col-md-12">

            {{-- Admin --}}
            @can('admin')
                <div class="alert alert-info text-center shadow-sm">
                    <h4 class="mb-0">👑 {{ __('Admin Panel') }}</h4>
                </div>
            @endcan
        </div>
        @endauth

    </div>
</div>
@endsection
