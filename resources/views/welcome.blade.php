@extends('layouts.app')

@section('content')
    @auth
     @can('client')
         <h1>Assalomu alaykum, mijoz!</h1>
     @endcan

     @can('driver')
     <h1>Assalomu alaykum, haydovchi!</h1>
     <div class="col-md-12">
         <div class="card shadow-lg">
             <img class="card-img-top img-fluid img-thumbnail" src="https://d3h1lg3ksw6i6b.cloudfront.net/media/image/2023/11/13/ccefc8ab770d4e98bfff372a28e1c73e_iStock-1415052246klein.jpg" class="card-img-top" alt="Lviv City">
                <div class="row">
                    <div class="col-6">
                        <div class="card-body">
                            <h3 class="card-title">Samarqand ➝ Toshkent</h3>
                            <h4 class="text-primary"><strong>50,000 UZS</strong></h4>
                            <p class="card-text text-muted">Boshidan: <strong>2025-01-01 18:30:00</strong></p>
                            <p class="card-text text-muted">Oxiridan: <strong>2025-01-07 20:30:00</strong></p>
                            <p class="card-text">Umumiy o‘rinlar: <strong>6</strong></p>
                            <p class="card-text">Bo‘sh o‘rinlar: <strong>2</strong></p>
                            <span class="badge bg-danger">Muddati tugagan</span>
                        </div>
                     </div>
                     <div class="col-6">
                        <div class="card ml-3 shadow-lg">
                            <div class="card-header bg-success text-white text-center">
                                <h4>Haydovchi haqida</h4>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">John Doe</h5>
                                <p class="card-text">Telefon: <strong>+998 90 123 45 67</strong></p>
                                <p class="card-text">Mashina: <strong>Chevrolet Cobalt</strong></p>
                                <p class="card-text">Davlat raqami: <strong>01 A 123 AA</strong></p>
                            </div>
                        </div>
                     </div>
                </div>
         </div>
     </div>
     @endcan





    @else
        <div class="text-center">
            <h1 class="mb-4">Assalomu alaykum!</h1>
            <h3 class="mb-4">Kim bo‘lib ro‘yxatdan o‘tasiz?</h3>

            <div class="d-flex justify-content-center gap-4">
                <a href="{{ route('client.auth.register.index') }}" class="btn btn-primary btn-lg px-5 py-3">
                    <i class="fas fa-user"></i> Mijoz
                </a>
                <a href="{{ route('driver.auth.register.index') }}" class="btn btn-success btn-lg px-5 py-3">
                    <i class="fas fa-car"></i> Haydovchi
                </a>
                <a href="{{ route('auth.login.index') }}" class="btn btn-warning btn-lg px-5 py-3">
                    <i class="fas fa-sign-in-alt"></i> Kirish
                </a>
            </div>
        </div>
    @endauth
@endsection
