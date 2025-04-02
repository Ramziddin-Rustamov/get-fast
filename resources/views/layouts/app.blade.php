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
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                        @else

                        @can('admin')
                        <li class="nav-item">
                            <a class="nav-link  {{ request()->routeIs('drivers.index') ? 'active bg-success rounded' : '' }}" href="{{ route('drivers.index') }}">
                                {{ __('Drivers') }}
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('clients.index') ? 'active bg-success rounded' : '' }}" href="{{ route('clients.index') }}">
                                {{ __('Clients') }}
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admins.index') ? 'active bg-success rounded' : '' }}" href="{{ route('admins.index') }}">
                                {{ __('Admins') }}
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('orders.index') ? 'active bg-success rounded' : '' }}" href="{{ route('orders.index') }}">
                                {{ __('Orders') }}
                            </a>
                        </li>   
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('driver-payments.index') ? 'active bg-success rounded' : '' }}" href="{{ route('driver-payments.index') }}">
                                {{ __('Driver Payment History') }}
                            </a>
                        </li>
                        
                        @endauth       
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">

                                {{-- Sozlamalar --}}
                                <a class="dropdown-item  {{ request()->routeIs('home') ? 'active bg-success rounded' : '' }}" href="{{ route('home') }}" >
                                    {{ __('Sozlamalar') }}
                                </a>
                                <div class="dropdown-divider"></div>

                                  {{-- Sozlamalar --}}
                                @can('driver_web')
                                <a class="dropdown-item  {{ request()->routeIs('profile.index.driver') ? 'active bg-success rounded' : '' }}" href="{{ route('profile.index.driver') }}" >
                                    {{ __('Ma\'lumotlarim') }}
                                </a>
                                <div class="dropdown-divider"></div>
                                @endcan

                                @can('client_web')
                                <a class="dropdown-item" href="{{ route('profile.index.client') }}" >
                                    {{ __('Ma\'lumotlarim') }}
                                </a>
                                <div class="dropdown-divider"></div>
                                @endcan

                                
                                
                                <a class="dropdown-item" href="{{ route('auth.logout.post') }}"
                                onclick="event.preventDefault();
                                                document.getElementById('logout-form').submit();">
                                    {{ __('Chiqish') }}
                                </a>

                                <form id="logout-form" action="{{ route('auth.logout.post') }}" method="POST" class="d-none">
                                    @csrf
                                </form>

                                <!-- Pastdan ajratuvchi chiziq -->

                            </div>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
 
        <main class="py-4  container">
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
