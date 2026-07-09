<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ketamiz.com')</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root { --k-acc-1: #16d39a; --k-acc-2: #0ea5e9; --k-ink: #0b1324; }
        body { background: #f4f7fb; font-family: 'Nunito', system-ui, sans-serif; color: var(--k-ink); }
        h1,h2,h3,h4,h5 { font-family: 'Sora','Nunito',sans-serif; letter-spacing: -.01em; }

        /* ---- Header ---- */
        .app-header {
            background: rgba(255,255,255,.85);
            backdrop-filter: saturate(180%) blur(12px);
            -webkit-backdrop-filter: saturate(180%) blur(12px);
            box-shadow: 0 6px 24px -14px rgba(11,19,36,.35);
        }
        .app-brand {
            font-family: 'Sora', sans-serif; font-weight: 800; font-size: 1.5rem;
            color: var(--k-ink); text-decoration: none; line-height: 1;
        }
        .app-brand .dot { color: var(--k-acc-1); }
        .app-header .nav-link { font-weight: 700; color: #334155; }
        .app-header .nav-link:hover { color: var(--k-acc-2); }

        .btn-k {
            background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
            color: #fff; font-weight: 800; border: none; border-radius: 12px;
            box-shadow: 0 10px 22px -10px rgba(22,211,154,.7);
        }
        .btn-k:hover { color: #fff; filter: brightness(1.05); transform: translateY(-1px); }
        .btn-k-ghost { font-weight: 800; color: var(--k-ink); border-radius: 12px; }
        .btn-k-ghost:hover { color: var(--k-acc-2); }

        .app-header .dropdown-menu {
            border: none; border-radius: 16px; padding: .5rem;
            box-shadow: 0 24px 60px -24px rgba(11,19,36,.45);
        }
        .app-header .dropdown-item { border-radius: 10px; font-weight: 600; padding: .55rem .8rem; }
        .app-header .dropdown-item:hover { background: #f1f5f9; }
        .app-header .dropdown-item.active, .app-header .dropdown-item.bg-success {
            background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2)) !important; color: #fff !important;
        }
    </style>

    @stack('styles')
</head>
<body>
    <div id="app">
        <header id="header" class="app-header sticky-top">
            <nav class="navbar navbar-expand-lg py-2">
                <div class="container">

                    <a class="app-brand me-auto" href="/">ketamiz<span class="dot">.com</span></a>

                    <div class="d-flex align-items-center gap-2">

                        {{-- Til almashtirgich (uz / ru / en) — barcha sahifada, default uz --}}
                        @php
                            $curLocale = app()->getLocale();
                            $locales = ['uz' => 'O‘zbekcha', 'en' => 'English'];
                            if (!isset($locales[$curLocale])) { $curLocale = 'uz'; }
                        @endphp
                        <div class="dropdown">
                            <button class="btn btn-k-ghost px-3 dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-globe me-1"></i> {{ strtoupper($curLocale) }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @foreach ($locales as $code => $name)
                                    <li>
                                        <a class="dropdown-item {{ $curLocale === $code ? 'active' : '' }}"
                                           href="{{ route('lang.switch', $code) }}">
                                            <span class="fw-bold me-1">{{ strtoupper($code) }}</span> — {{ $name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        @guest
                            @if (Route::has('register'))
                                <a class="btn btn-k px-3 py-2 {{ Request::is('register') ? 'active' : '' }}"
                                   href="{{ route('register') }}">
                                    {{ __('Ro\'yxatdan o\'tish') }}
                                </a>
                            @endif
                        @else
                            <div class="dropdown">
                                <a id="navbarDropdown" class="btn btn-k px-3 py-2 dropdown-toggle" href="#" role="button"
                                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-table-columns me-1"></i> {{ __('Boshqaruv') }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">

                                    @can('admin')
                                        <a class="dropdown-item {{ request()->routeIs('drivers.index') ? 'active' : '' }}" href="{{ route('drivers.index') }}">
                                            <i class="fas fa-id-card me-2"></i>{{ __('Drivers') }}
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('clients.index') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                                            <i class="fas fa-users me-2"></i>{{ __('Clients') }}
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('admin.withdraw.index') ? 'active' : '' }}" href="{{ route('admin.withdraw.index') }}">
                                            <i class="fas fa-money-bill-transfer me-2"></i>{{ __('Withdraws') }}
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('company.dashboard') ? 'active' : '' }}" href="{{ route('company.dashboard') }}">
                                            <i class="fas fa-chart-line me-2"></i>{{ __('Company Dashboard') }}
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('company.transactions') ? 'active' : '' }}" href="{{ route('company.transactions') }}">
                                            <i class="fas fa-receipt me-2"></i>{{ __('Company Transactions') }}
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('orders.index') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                                            <i class="fas fa-box-open me-2"></i>{{ __('Orders') }}
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('payments.index') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                                            <i class="fas fa-credit-card me-2"></i>{{ __('Payments') }}
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('support.index') ? 'active' : '' }}" href="{{ route('support.index') }}">
                                            <i class="fas fa-headset me-2"></i>{{ __('Support Help') }}
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('broadcasts.*') ? 'active' : '' }}" href="{{ route('broadcasts.index') }}">
                                            <i class="fas fa-bullhorn me-2"></i>{{ __('E\'lonlar (Push)') }}
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('parcel-types.*') ? 'active' : '' }}" href="{{ route('parcel-types.index') }}">
                                            <i class="fas fa-box me-2"></i>{{ __('Pochta turlari') }}
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('search-logs.*') ? 'active' : '' }}" href="{{ route('search-logs.index') }}">
                                            <i class="fas fa-magnifying-glass me-2"></i>{{ __('Qidiruvlar') }}
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    @endcan

                                    @can('driver_web')
                                        <a class="dropdown-item {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                            <i class="fas fa-circle-user me-2"></i>{{ __('Ma\'lumotlarim') }}
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    @endcan

                                    @can('client_web')
                                        <a class="dropdown-item {{ request()->routeIs('/') ? 'active' : '' }}" href="/">
                                            <i class="fas fa-magnifying-glass me-2"></i>{{ __('Book Trip') }}
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('profile.index.client') ? 'active' : '' }}" href="{{ route('profile.index.client') }}">
                                            <i class="fas fa-user me-2"></i>{{ __('Profile') }}
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('client.trips.index') ? 'active' : '' }}" href="{{ route('client.trips.index') }}">
                                            <i class="fas fa-route me-2"></i>{{ __('My booked trips') }}
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('client.parcels.index') ? 'active' : '' }}" href="{{ route('client.parcels.index') }}">
                                            <i class="fas fa-box me-2"></i>{{ __('My parcels') }}
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    @endcan

                                    <a class="dropdown-item text-danger fw-bold" href="{{ route('auth.logout.post') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-right-from-bracket me-2"></i>{{ __('Chiqish') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('auth.logout.post') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </div>
                        @endguest
                    </div>
                </div>
            </nav>
        </header>

        <main class="pb-4">
            @yield('content')
        </main>
    </div>

    @yield('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
