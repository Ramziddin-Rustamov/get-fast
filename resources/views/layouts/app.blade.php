<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha384-qIgtX3TJL3zI6AOMsBoC3RnUedbLgPoLm1fIxSkKpTME4xD9FfJpLzQ2Np9nXKFN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        @stack('styles')
</head>
<body style="background-color: rgb(220, 213, 213)">
    <div id="app">
        <header id="header" class="fixed-top  pb-2" style="background-color: rgba(255, 244, 239, 0.8); box-shadow: 11px 11px 35px -10px rgba(66, 68, 90, 1);">
            <div class="container d-flex align-items-center">
        
                <h4 class="logo me-auto">
                    <a href="/" class="text-decoration-none"> {{ config('app.name', 'Qadam') }}</a>
                </h4>
        
        
              <div class="header-social-links ps-2 d-flex py-2">
                <ul class="navbar-nav ms-auto d-md-flex d-lg-flex ">

                    
                    <!-- Authentication Links -->
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item ">
                                <a style="{{ (Request::is('login') ? 'color: green; text-decoration: none;' : '') }}" class="nav-link " href="{{ route('login') }}"><span>{{ __('Kirish') }}</span></a>
                            </li>
                        @endif
        
                        @if (Route::has('register'))
                            <li class="nav-item active">
                                <a style="{{ (Request::is('register') ? 'color: green; text-decoration: underline;' : '') }}"  class="nav-link {{ (Request::is('register') ? 'active' : '') }}" href="{{ route('register') }}"><span>{{ __('R. O\'tish') }}</span></a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown d-flex "  id="navbarDropdown" >
                            <a style="padding-top:16px" id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                               {{__("Boshqaruv")}}
                            </a>
        
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown" style="background-color: rgba(255, 244, 239, 1);
                            box-shadow: 11px 11px 35px -10px rgba(66, 68, 90, 1);">


                            @can('admin')
                            <ul class="navbar-nav">
                                <li class="nav-item ">
                                    <a class="dropdown-item {{ request()->routeIs('drivers.index') ? 'active bg-success rounded' : '' }}"
                                       href="{{ route('drivers.index') }}">
                                        {{ __('Drivers') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="dropdown-item  {{ request()->routeIs('clients.index') ? 'active bg-success rounded' : '' }}"
                                       href="{{ route('clients.index') }}">
                                        {{ __('Clients') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="dropdown-item  {{ request()->routeIs('admins.index') ? 'active bg-success rounded' : '' }}"
                                       href="{{ route('admins.index') }}">
                                        {{ __('Admins') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="dropdown-item  {{ request()->routeIs('orders.index') ? 'active bg-success rounded' : '' }}"
                                       href="{{ route('orders.index') }}">
                                        {{ __('Orders') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    {{-- <a class="dropdown-item  {{ request()->routeIs('driver-payments.index') ? 'active bg-success rounded' : '' }}"
                                       href="{{ route('driver-payments.index') }}">
                                        {{ __('Driver Payment History') }}
                                    </a> --}}
                                </li>
                            </ul>
                        @endcan
                             

                        @can('driver_web')
                            <a class="dropdown-item {{ request()->routeIs('home') ? 'active bg-success rounded' : '' }}"
                            href="{{ route('home') }}">
                                {{ __('Ma\'lumotlarim') }}
                            </a>
                            <div class="dropdown-divider"></div>
                        @endcan

                        @can('client_web') 
                        <a class="dropdown-item {{ request()->routeIs('/') ? 'active bg-success rounded' : '' }}" href="/">
                            {{ __('Book Trip') }}
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item {{ request()->routeIs('profile.index.client') ? 'active bg-success rounded' : '' }}" href="{{ route('profile.index.client') }}">
                            {{ __('Profile') }}
                        </a>
                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item {{ request()->routeIs('client.trips.index') ? 'active bg-success rounded' : '' }}" href="{{ route('client.trips.index') }}">
                            {{ __('My booked trips') }}
                        </a>
                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item {{ request()->routeIs('client.parcels.index') ? 'active bg-success rounded' : '' }} " href="{{ route('client.parcels.index') }}">
                            {{ __('My parcels') }}
                        </a>
                        <div class="dropdown-divider"></div>

                    @endcan


                    <a class="dropdown-item" href="{{ route('auth.logout.post') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        {{ __('Chiqish') }}
                    </a>

                    <form id="logout-form" action="{{ route('auth.logout.post') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                            </div>
                        </li>
                    @endguest
                  </ul>
                 <!-- Right Side Of Navbar -->
              </div>
            </div>
            {{-- <h6 class="moving-text">Ushbu tizim hozircha test rejimda ishlamoqda ... </h6> --}}
        </header>
        
 
        <main class="py-4 mt-5 container">
            @yield('content')
        </main>
    </div>
    
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @yield('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
