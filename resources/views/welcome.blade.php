@extends('layouts.app')

@section('content')
@auth
    
<div class="vh-100 d-flex align-items-center justify-content-center" >
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0" style="border-radius: 15px;">
                    <div class="card-header bg-primary text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                        <h3 class="mb-0">Boshqaruv Panelga Xush Kelibsiz</h3>
                    </div>
                    <div class="card-body p-5" style="background: #f7f9fc; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                        <p class="lead mb-4">Assalomu alaykum, <strong>{{ Auth::user()->first_name ?? Auth::user()->phone }}</strong>!</p>
                        <p class="mb-4">Sizning boshqaruv panelingizdan barcha imkoniyatlardan foydalanishingiz mumkin.</p>

                        <a href="{{ route('auth.logout.post') }}" 
                           class="btn btn-danger btn-lg"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                           Chiqish
                        </a>

                        <form id="logout-form" action="{{ route('auth.logout.post') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endauth

@guest
     <div>
            Hello 
     </div>
@endguest
@endsection
