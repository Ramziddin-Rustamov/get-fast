@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    @auth
     @can('client')
         <h1>Assalomu alaykum, mijoz!</h1>
     @endcan

     @can('driver')
         <h1>Assalomu alaykum, haydovchi!</h1>
     @endcan
    @else
        <div class="text-center">
            <h1 class="mb-4">Assalomu alaykum!</h1>
            <h3 class="mb-4">Kim bo‘lib ro‘yxatdan o‘tasiz?</h3>

            <div class="d-flex justify-content-center gap-4">
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5 py-3">
                    <i class="fas fa-user"></i> Mijoz
                </a>
                <a href="{{ route('login') }}" class="btn btn-success btn-lg px-5 py-3">
                    <i class="fas fa-car"></i> Haydovchi
                </a>
                <a href="{{ route('login') }}" class="btn btn-warning btn-lg px-5 py-3">
                    <i class="fas fa-sign-in-alt"></i> Kirish
                </a>
            </div>
        </div>
    @endauth
</div>
@endsection
