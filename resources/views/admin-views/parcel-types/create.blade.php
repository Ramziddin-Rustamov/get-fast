@extends('layouts.app')

@section('title', 'Yangi pochta turi')

@section('content')

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <h3 class="fw-bold text-dark mb-4">📦 Yangi pochta turi</h3>

            @if($errors->any())
                <div class="alert alert-danger rounded-3">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('parcel-types.store') }}">
                        @include('admin-views.parcel-types._form')
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
