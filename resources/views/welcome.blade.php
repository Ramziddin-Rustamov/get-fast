@extends('layouts.app')

@section('title', 'ketamiz.com — O‘zbekiston bo‘ylab birga safar')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    /* ====== ketamiz.com landing — hammasi .ketamiz ostida scoped ====== */
    .ketamiz {
        --dark:#091022; --dark2:#0e1730; --acc1:#16d39a; --acc2:#0ea5e9;
        --ink:#0b1324; --muted:#64748b; --soft:#f4f7fb;
        font-family:'Nunito',system-ui,sans-serif; color:var(--ink); overflow-x:hidden;
    }
    .ketamiz h1,.ketamiz h2,.ketamiz h3,.ketamiz h4,.ketamiz h5,.ketamiz .wm{
        font-family:'Sora','Nunito',sans-serif; letter-spacing:-.02em;
    }
    .ketamiz .grad{ background:linear-gradient(135deg,var(--acc1),var(--acc2)); }
    .ketamiz .grad-text{ background:linear-gradient(135deg,var(--acc1),var(--acc2));
        -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
    .ketamiz .wm{ font-weight:800; } .ketamiz .wm .dot{ color:var(--acc1); }
    .ketamiz .chip{ display:inline-flex; align-items:center; gap:.5rem; padding:.4rem .9rem;
        border-radius:999px; font-weight:700; font-size:.85rem; background:rgba(22,211,154,.12); color:#0c8f64; }
    .ketamiz .chip-d{ background:rgba(255,255,255,.1); color:#aef3da; }

    /* HERO (fixed header uchun yuqoridan joy) */
    .ketamiz .hero{ position:relative; padding:70px 0 90px; color:#fff;
        background:
            radial-gradient(900px 480px at 85% -5%, rgba(14,165,233,.30), transparent 60%),
            radial-gradient(820px 480px at 0% 12%, rgba(22,211,154,.22), transparent 55%),
            var(--dark); }
    .ketamiz .hero::after{ content:""; position:absolute; left:0; right:0; bottom:-1px; height:70px;
        background:linear-gradient(180deg,transparent,var(--soft)); }
    .ketamiz .hero h1{ font-size:clamp(2.3rem,5.5vw,4rem); font-weight:800; line-height:1.06; }
    .ketamiz .hero .sub{ color:#aab4c8; font-size:1.16rem; max-width:540px; }

    /* STORE BADGES */
    .ketamiz .store{ display:inline-flex; align-items:center; gap:.7rem; padding:.65rem 1.3rem;
        border-radius:14px; background:#fff; color:#0a1124; text-decoration:none;
        transition:transform .25s,box-shadow .25s; box-shadow:0 12px 28px rgba(0,0,0,.3); }
    .ketamiz .store:hover{ transform:translateY(-4px); box-shadow:0 20px 40px rgba(0,0,0,.42); color:#0a1124; }
    .ketamiz .store i{ font-size:1.85rem; }
    .ketamiz .store .t{ display:flex; flex-direction:column; line-height:1.1; text-align:left; }
    .ketamiz .store .t small{ font-size:.66rem; opacity:.7; }
    .ketamiz .store .t strong{ font-size:1.05rem; font-weight:800; }
    .ketamiz .store.ol{ background:transparent; color:#fff; box-shadow:inset 0 0 0 1.5px rgba(255,255,255,.25); }
    .ketamiz .store.ol:hover{ color:#fff; background:rgba(255,255,255,.06); }

    /* TRIP CARD (hero visual) */
    .ketamiz .tripcard{ background:#fff; color:var(--ink); border-radius:22px; padding:1.3rem;
        box-shadow:0 40px 80px -30px rgba(0,0,0,.6); max-width:400px; margin:0 auto; }
    .ketamiz .tripcard .row-line{ display:flex; align-items:center; gap:.6rem; padding:.7rem 0; }
    .ketamiz .tripcard .pin{ width:12px; height:12px; border-radius:50%; flex-shrink:0; }
    .ketamiz .tripcard .stem{ width:2px; height:26px; margin-left:5px; background:repeating-linear-gradient(180deg,#cbd5e1 0 4px,transparent 4px 8px); }
    .ketamiz .tripcard .who{ display:flex; align-items:center; gap:.6rem; padding:.6rem; background:var(--soft); border-radius:14px; }
    .ketamiz .tripcard .av{ width:40px; height:40px; border-radius:50%; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; }

    /* MARQUEE */
    .ketamiz .marq{ background:var(--soft); padding:1rem 0; overflow:hidden; white-space:nowrap; }
    .ketamiz .marq .trk{ display:inline-block; animation:kmv 26s linear infinite; }
    .ketamiz .marq span{ font-family:'Sora'; font-weight:700; color:#94a3b8; font-size:1.3rem; margin:0 1.4rem; }
    .ketamiz .marq span i{ color:var(--acc1); font-size:.6rem; vertical-align:middle; margin-left:1.4rem; }
    @keyframes kmv{ from{transform:translateX(0);} to{transform:translateX(-50%);} }

    /* STORY */
    .ketamiz .story{ background:#fff; border:1px solid #eef2f7; border-radius:28px; overflow:hidden;
        box-shadow:0 40px 80px -45px rgba(11,19,36,.5); }
    .ketamiz .story .head{ padding:2rem; }
    .ketamiz .person{ background:var(--soft); border-radius:20px; padding:1.5rem; height:100%; }
    .ketamiz .person .ic{ width:58px; height:58px; border-radius:16px; color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.5rem; margin-bottom:1rem; }
    .ketamiz .save{ background:linear-gradient(135deg,#052e23,#06283d); color:#fff; border-radius:20px; padding:1.5rem; height:100%; }
    .ketamiz .save .big{ font-family:'Sora'; font-weight:800; font-size:1.6rem; }
    .ketamiz .road{ position:relative; height:70px; display:flex; align-items:center; justify-content:center; }
    .ketamiz .road .ln{ position:absolute; left:8%; right:8%; height:3px; background:repeating-linear-gradient(90deg,var(--acc1) 0 14px,transparent 14px 26px); }
    .ketamiz .road .car{ position:relative; z-index:1; width:56px; height:56px; border-radius:50%; color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.4rem; box-shadow:0 14px 30px -8px rgba(22,211,154,.7); }

    /* STORY v2 — kengaytirilgan */
    .ketamiz .scene{ background:linear-gradient(135deg,#0b1430,#0e1c3f); border-radius:26px; padding:1.8rem 2rem; color:#fff; position:relative; overflow:hidden; }
    .ketamiz .scene::after{ content:""; position:absolute; inset:0; background:radial-gradient(520px 220px at 80% 0,rgba(14,165,233,.3),transparent 60%); }
    .ketamiz .scene > *{ position:relative; z-index:1; }
    .ketamiz .scene .stops{ display:flex; align-items:center; gap:1rem; }
    .ketamiz .scene .stop{ text-align:center; flex-shrink:0; width:130px; }
    .ketamiz .scene .stop .dotmark{ width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.2rem; margin:0 auto .5rem; }
    .ketamiz .scene .path{ flex:1; height:3px; position:relative; background:repeating-linear-gradient(90deg,rgba(255,255,255,.4) 0 12px,transparent 12px 24px); }
    .ketamiz .scene .path .moving{ position:absolute; top:-22px; font-size:1.7rem; display:inline-block; transform:scaleX(-1); animation:kdrive 4.5s linear infinite; }
    @keyframes kdrive{ from{left:-5%;} to{left:100%;} }

    .ketamiz .jstep{ display:flex; gap:.9rem; padding:1rem; background:#fff; border:1px solid #eef2f7; border-radius:18px; height:100%; }
    .ketamiz .jstep .jn{ width:40px; height:40px; flex-shrink:0; border-radius:12px; color:#fff; display:flex; align-items:center; justify-content:center; font-family:'Sora'; font-weight:800; }

    .ketamiz .vs{ background:#fff; border:1px solid #eef2f7; border-radius:24px; overflow:hidden; height:100%;
        box-shadow:0 24px 50px -32px rgba(11,19,36,.4); transition:transform .3s, box-shadow .3s; }
    .ketamiz .vs:hover{ transform:translateY(-6px); box-shadow:0 32px 60px -30px rgba(14,165,233,.4); }
    .ketamiz .vs .vs-head{ padding:1.1rem 1.4rem; display:flex; align-items:center; gap:.7rem; color:#fff;
        font-family:'Sora','Nunito',sans-serif; font-weight:800; font-size:1.08rem; }
    .ketamiz .vs .vs-head .hic{ width:38px; height:38px; border-radius:11px; background:rgba(255,255,255,.22);
        display:flex; align-items:center; justify-content:center; }
    .ketamiz .vs-bad .vs-head{ background:linear-gradient(135deg,#fb7185,#ef4444); }
    .ketamiz .vs-good .vs-head{ background:linear-gradient(135deg,#16d39a,#0ea5e9); }
    .ketamiz .vs ul{ list-style:none; margin:0; padding:.6rem 1.4rem 1.2rem; }
    .ketamiz .vs li{ display:flex; align-items:center; gap:.85rem; padding:.6rem 0; color:#334155; border-bottom:1px dashed #eef2f7; }
    .ketamiz .vs li:last-child{ border-bottom:none; }
    .ketamiz .vs li .lic{ width:34px; height:34px; border-radius:10px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:.9rem; }
    .ketamiz .vs-bad li .lic{ background:#fee2e2; color:#ef4444; }
    .ketamiz .vs-good li .lic{ background:#dcfce7; color:#16a34a; }
    .ketamiz .vs-vs{ position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); z-index:3;
        width:58px; height:58px; border-radius:50%; background:#0b1324; color:#fff;
        font-family:'Sora'; font-weight:800; display:flex; align-items:center; justify-content:center;
        box-shadow:0 14px 32px rgba(11,19,36,.45); border:4px solid var(--soft); }

    .ketamiz .numbox{ background:#fff; border:1px solid #eef2f7; border-radius:18px; padding:1.3rem; text-align:center; height:100%; }
    .ketamiz .numbox .micon{ width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; color:#fff; margin:0 auto .6rem; font-size:1.2rem; }
    .ketamiz .numbox .was{ text-decoration:line-through; color:#94a3b8; }
    .ketamiz .numbox .now{ font-family:'Sora'; font-weight:800; font-size:1.4rem; }

    /* TILES */
    .ketamiz .tile{ background:#fff; border:1px solid #eef2f7; border-radius:22px; padding:1.8rem; height:100%;
        transition:transform .3s,box-shadow .3s,border-color .3s; }
    .ketamiz .tile:hover{ transform:translateY(-8px); box-shadow:0 28px 55px -28px rgba(14,165,233,.45); border-color:rgba(22,211,154,.35); }
    .ketamiz .tile .ic{ width:56px; height:56px; border-radius:16px; color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.5rem; margin-bottom:1rem; }
    .ketamiz .g-green{ background:linear-gradient(135deg,#16d39a,#0ea5e9); }
    .ketamiz .g-blue{ background:linear-gradient(135deg,#0ea5e9,#6366f1); }
    .ketamiz .g-amber{ background:linear-gradient(135deg,#fbbf24,#f59e0b); }
    .ketamiz .g-rose{ background:linear-gradient(135deg,#fb7185,#ef4444); }

    /* STEPS */
    .ketamiz .step .n{ width:64px; height:64px; margin:0 auto 1rem; border-radius:50%; color:#fff;
        display:flex; align-items:center; justify-content:center; font-family:'Sora'; font-weight:800; font-size:1.4rem;
        box-shadow:0 16px 30px -10px rgba(22,211,154,.6); }

    /* REVEAL (scroll animatsiya) */
    .ketamiz .reveal{ opacity:0; transform:translateY(30px); transition:opacity .6s ease, transform .6s ease; }
    .ketamiz .reveal.show{ opacity:1; transform:none; }

    /* APP SCREENSHOTS — telefon ichida */
    .ketamiz .device{ width:300px; max-width:78vw; margin:0 auto; aspect-ratio:.485;
        background:#0c1230; border-radius:44px; padding:13px; position:relative;
        box-shadow:0 50px 95px -32px rgba(14,165,233,.55), inset 0 0 0 2px rgba(255,255,255,.06);
        animation:pfloat 5.5s ease-in-out infinite; }
    .ketamiz .device::before{ content:""; position:absolute; top:15px; left:50%; transform:translateX(-50%);
        width:120px; height:22px; background:#0c1230; border-radius:0 0 16px 16px; z-index:3; }
    .ketamiz .device-screen{ width:100%; height:100%; border-radius:33px; overflow:hidden; background:#000; }
    .ketamiz .device .carousel, .ketamiz .device .carousel-inner, .ketamiz .device .carousel-item{ height:100%; }
    .ketamiz .device .carousel-item img{ width:100%; height:100%; object-fit:cover; }
    @keyframes pfloat{ 0%,100%{ transform:translateY(0); } 50%{ transform:translateY(-14px); } }

    .ketamiz .shot-feat{ display:flex; gap:.7rem; align-items:flex-start; padding:.5rem 0; }
    .ketamiz .shot-feat i{ color:var(--acc1); margin-top:.25rem; }
    .ketamiz .dots{ display:flex; flex-wrap:wrap; gap:6px; justify-content:center; margin-top:1.2rem; }
    .ketamiz .dots button{ width:8px; height:8px; border-radius:999px; border:none; background:#cbd5e1; padding:0; opacity:.7; }
    .ketamiz .dots button.active{ background:linear-gradient(135deg,var(--acc1),var(--acc2)); width:22px; opacity:1; }

    /* SCENARIOS (vaziyatlar) */
    .ketamiz .scenario{ background:#fff; border:1px solid #eef2f7; border-radius:20px; padding:1.5rem; height:100%;
        transition:transform .3s, box-shadow .3s, border-color .3s; }
    .ketamiz .scenario:hover{ transform:translateY(-8px); box-shadow:0 28px 55px -28px rgba(14,165,233,.45); border-color:rgba(22,211,154,.35); }
    .ketamiz .scenario .ic{ width:54px; height:54px; border-radius:15px; color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.35rem; margin-bottom:1rem; }
    .ketamiz .scenario h5{ font-weight:800; margin-bottom:.5rem; }
    .ketamiz .scenario .wintag{ display:inline-flex; align-items:center; gap:.4rem; margin-top:1rem;
        font-weight:700; font-size:.78rem; color:#0c8f64; background:rgba(22,211,154,.12); padding:.35rem .8rem; border-radius:999px; }

    /* DOWNLOAD */
    .ketamiz .dl{ background:radial-gradient(600px 300px at 85% 10%, rgba(14,165,233,.4), transparent 60%), var(--dark2);
        border-radius:30px; padding:4rem 2rem; color:#fff; }

    /* FOOTER */
    .ketamiz .ft{ background:var(--dark); color:#94a3b8; padding:3rem 0 1.8rem; }
    .ketamiz .ft a{ color:#94a3b8; text-decoration:none; } .ketamiz .ft a:hover{ color:#fff; }

    /* AUTH */
    .ketamiz .auth{ min-height:90vh; display:flex; align-items:center;
        background:radial-gradient(800px 400px at 80% 0%, rgba(14,165,233,.25), transparent 60%), var(--dark); padding:60px 0; }
    .ketamiz .auth-card{ background:#fff; border-radius:26px; overflow:hidden; box-shadow:0 50px 90px -40px rgba(0,0,0,.7); }

    @media (max-width:991px){
        .ketamiz .hero{ text-align:center; }
        .ketamiz .hero .sub{ margin-inline:auto; }
        .ketamiz .hero .acts{ justify-content:center; }
        .ketamiz .road .ln{ display:none; }
    }
</style>
@endpush

@section('content')
<div class="ketamiz">

@auth
    <section class="auth">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="auth-card">
                        <div class="grad text-white text-center py-5 px-4">
                            <div class="wm fs-2 mb-2">ketamiz<span class="dot">.com</span></div>
                            <p class="mb-0 opacity-75">{{ __('welcome.auth_panel') }}</p>
                        </div>
                        <div class="text-center p-5">
                            <h4 class="fw-bold mb-3">{{ __('welcome.auth_hello') }},
                                <span class="grad-text">{{ Auth::user()->first_name ?? Auth::user()->phone }}</span>!
                            </h4>
                            <p class="text-muted mb-4">{{ __('welcome.auth_welcome_msg') }}</p>
                            <a href="{{ route('auth.logout.post') }}"
                               class="btn btn-lg px-4 grad text-white fw-bold" style="border-radius:14px;"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                               <i class="fas fa-right-from-bracket me-2"></i> {{ __('welcome.logout') }}
                            </a>
                            <form id="logout-form" action="{{ route('auth.logout.post') }}" method="POST" class="d-none">@csrf</form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endauth

@guest
    {{-- ============================= HERO ============================= --}}
    <section class="hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="chip chip-d animate__animated animate__fadeInDown">
                        <i class="fas fa-location-dot"></i> {{ __('welcome.hero_badge') }}
                    </span>
                    <h1 class="mt-4 animate__animated animate__fadeInUp">
                        {{ __('welcome.hero_title_before') }}<span class="grad-text">{{ __('welcome.hero_title_accent') }}</span>{{ __('welcome.hero_title_after') }}
                    </h1>
                    <p class="sub mt-3 animate__animated animate__fadeInUp">
                        {{ __('welcome.hero_sub') }}
                    </p>

                    <div class="acts d-flex flex-wrap gap-3 mt-4 animate__animated animate__fadeInUp">
                        <a href="https://play.google.com/store/apps/details?id=uz.ketamiz.app" class="store ol"><i class="fab fa-google-play"></i>
                        <a href="https://apps.apple.com/pl/app/ketamiz/id6782315775" class="store"><i class="fab fa-apple"></i>
                            <span class="t"><small>{{ __('welcome.download') }}</small><strong>App Store</strong></span></a>
                        <a href="https://play.google.com/store/apps/details?id=uz.ketamiz.app" class="store ol"><i class="fab fa-google-play"></i>
                            <span class="t"><small>{{ __('welcome.download') }}</small><strong>Google Play</strong></span></a>
                    </div>

                    <div class="d-flex flex-wrap gap-4 mt-4" style="color:#94a3b8;">
                        <span><i class="fas fa-wallet grad-text me-1"></i> {{ __('welcome.tag_save') }}</span>
                        <span><i class="fas fa-leaf grad-text me-1"></i> {{ __('welcome.tag_eco') }}</span>
                        <span><i class="fas fa-shield-halved grad-text me-1"></i> {{ __('welcome.tag_safe') }}</span>
                    </div>
                </div>

                {{-- Trip card visual --}}
                <div class="col-lg-6">
                    <div class="tripcard animate__animated animate__fadeInRight">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold"><i class="fas fa-calendar-day grad-text me-1"></i> {{ __('welcome.planned_trip') }}</span>
                            <span class="chip">{{ __('welcome.tomorrow_time') }}</span>
                        </div>
                        <div class="row-line"><span class="pin grad"></span> <div><div class="small text-muted">{{ __('welcome.from') }}</div><strong>{{ __('welcome.trip_from') }}</strong></div></div>
                        <div class="stem"></div>
                        <div class="row-line"><span class="pin" style="background:#ef4444;"></span> <div><div class="small text-muted">{{ __('welcome.to') }}</div><strong>{{ __('welcome.trip_to') }}</strong></div></div>
                        <hr>
                        <div class="row g-2">
                            <div class="col-6"><div class="who"><span class="av g-blue">D</span><div><div class="small text-muted">{{ __('welcome.driver') }}</div><strong>{{ __('welcome.teacher_short') }}</strong></div></div></div>
                            <div class="col-6"><div class="who"><span class="av g-green">T</span><div><div class="small text-muted">{{ __('welcome.passenger') }}</div><strong>{{ __('welcome.student_short') }}</strong></div></div></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 p-2 rounded-3" style="background:#ecfdf5;">
                            <span class="fw-bold text-success"><i class="fas fa-tag me-1"></i> {{ __('welcome.one_seat') }}</span>
                            <span class="fw-bold fs-5 text-success">12 000 so‘m</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================= MARQUEE (viloyatlar) ============================= --}}
    <div class="marq">
        <div class="trk">
            @php $regions = ['Toshkent','Samarqand','Buxoro','Andijon','Farg‘ona','Namangan','Qashqadaryo','Surxondaryo','Jizzax','Sirdaryo','Navoiy','Xorazm','Qoraqalpog‘iston']; @endphp
            @foreach(array_merge($regions, $regions) as $r)<span>{{ $r }} <i class="fas fa-circle"></i></span>@endforeach
        </div>
    </div>

    {{-- ============================= STORY (kengaytirilgan) ============================= --}}
    <section class="py-5" style="background:var(--soft);">
        <div class="container py-3">
            <div class="text-center mb-5">
                <span class="chip"><i class="fas fa-lightbulb"></i> {{ __('welcome.story_badge') }}</span>
                <h2 class="fw-bold display-6 mt-3">{{ __('welcome.story_title') }}</h2>
                <p class="text-muted mx-auto" style="max-width:740px;">{{ __('welcome.story_sub') }}</p>
            </div>

            {{-- Sahna: marshrut --}}
            <div class="scene mb-4 animate__animated animate__fadeInUp">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <span class="chip chip-d"><i class="fas fa-calendar-check"></i> {{ __('welcome.planned_trip') }} · {{ __('welcome.tomorrow_time') }}</span>
                    <span class="chip chip-d"><i class="fas fa-route"></i> {{ __('welcome.scene_route') }}</span>
                </div>
                <div class="stops">
                    <div class="stop">
                        <div class="dotmark grad"><i class="fas fa-house"></i></div>
                        <div class="fw-bold">{{ __('welcome.scene_from') }}</div>
                        <div class="small opacity-75">{{ __('welcome.scene_from_sub') }}</div>
                    </div>
                    <div class="path"><span class="moving">🚗</span></div>
                    <div class="stop">
                        <div class="dotmark" style="background:#ef4444;"><i class="fas fa-graduation-cap"></i></div>
                        <div class="fw-bold">{{ __('welcome.scene_to') }}</div>
                        <div class="small opacity-75">{{ __('welcome.scene_to_sub') }}</div>
                    </div>
                </div>
            </div>

            {{-- Hikoya bosqichlari --}}
            @php $jColors = ['g-blue','g-amber','g-green','grad']; @endphp
            <div class="row g-3 mb-4">
                @foreach(__('welcome.journey') as $i => $j)
                    <div class="col-lg-3 col-sm-6"><div class="jstep"><span class="jn {{ $jColors[$i] ?? 'grad' }}">{{ $i + 1 }}</span><div><strong>{{ $j['t'] }}</strong><div class="text-muted small">{{ $j['d'] }}</div></div></div></div>
                @endforeach
            </div>

            {{-- Avval vs ketamiz bilan --}}
            <div class="row g-4 mb-4 position-relative">
                <div class="col-md-6">
                    <div class="vs vs-bad">
                        <div class="vs-head"><span class="hic"><i class="fas fa-circle-xmark"></i></span> {{ __('welcome.without_title') }}</div>
                        @php $badIcons = ['fa-car','fa-taxi','fa-gas-pump','fa-smog']; @endphp
                        <ul>
                            @foreach(__('welcome.without') as $i => $line)
                                <li><span class="lic"><i class="fas {{ $badIcons[$i] ?? 'fa-xmark' }}"></i></span> <span>{{ $line }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="vs vs-good">
                        <div class="vs-head"><span class="hic"><i class="fas fa-circle-check"></i></span> {{ __('welcome.with_title') }}</div>
                        @php $goodIcons = ['fa-car-side','fa-wallet','fa-hand-holding-dollar','fa-leaf']; @endphp
                        <ul>
                            @foreach(__('welcome.with') as $i => $line)
                                <li><span class="lic"><i class="fas {{ $goodIcons[$i] ?? 'fa-check' }}"></i></span> <span>{{ $line }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="vs-vs d-none d-md-flex">VS</div>
            </div>

            {{-- Tejamkorlik raqamlari --}}
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="numbox">
                        <div class="micon g-green"><i class="fas fa-user-graduate"></i></div>
                        <div class="text-muted small">{{ __('welcome.num_student') }}</div>
                        <div><span class="was">30 000</span> &rarr; <span class="now text-success">12 000 so‘m</span></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="numbox">
                        <div class="micon g-blue"><i class="fas fa-gas-pump"></i></div>
                        <div class="text-muted small">{{ __('welcome.num_fuel') }}</div>
                        <div><span class="was">40 000</span> &rarr; <span class="now text-success">{{ __('welcome.num_covered') }}</span></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="numbox">
                        <div class="micon g-amber"><i class="fas fa-piggy-bank"></i></div>
                        <div class="text-muted small">{{ __('welcome.num_month') }}</div>
                        <div class="now text-success">~400 000 so‘m</div>
                        <div class="small text-muted">{{ __('welcome.num_month_sub') }}</div>
                    </div>
                </div>
            </div>

            {{-- Viloyatlar banneri --}}
            <div class="mt-4 p-4 rounded-4 text-center" style="background:linear-gradient(135deg,#ecfdf5,#e0f2fe);">
                <i class="fas fa-arrows-left-right grad-text me-1"></i>
                <strong>{{ __('welcome.region_banner_strong') }}</strong>
                {{ __('welcome.region_banner') }}
            </div>
        </div>
    </section>

    {{-- ============================= SCENARIOS (yana vaziyatlar) ============================= --}}
    <section class="py-5 bg-white">
        <div class="container py-3">
            <div class="text-center mb-5">
                <span class="chip"><i class="fas fa-people-arrows"></i> {{ __('welcome.scenarios_badge') }}</span>
                <h2 class="fw-bold display-6 mt-3">{{ __('welcome.scenarios_title') }}</h2>
                <p class="text-muted mx-auto" style="max-width:700px;">{{ __('welcome.scenarios_sub') }}</p>
            </div>

            @php
                // Ikonka va rang (matnlar resources/lang/*/welcome.php dan olinadi)
                $scMeta = [
                    ['fa-chalkboard-user','g-blue'], ['fa-briefcase','g-green'], ['fa-city','g-amber'], ['fa-store','g-rose'],
                    ['fa-graduation-cap','g-green'], ['fa-box','g-blue'], ['fa-people-group','g-amber'], ['fa-plane-departure','g-rose'],
                ];
            @endphp

            <div class="row g-4">
                @foreach(__('welcome.scenarios') as $i => $s)
                    <div class="col-lg-3 col-md-6 reveal" style="transition-delay: {{ ($i % 4) * 100 }}ms;">
                        <div class="scenario">
                            <div class="ic {{ $scMeta[$i][1] ?? 'g-green' }}"><i class="fas {{ $scMeta[$i][0] ?? 'fa-circle-check' }}"></i></div>
                            <h5>{{ $s['title'] }}</h5>
                            <p class="text-muted small mb-0">{{ $s['desc'] }}</p>
                            <span class="wintag"><i class="fas fa-circle-check"></i> {{ $s['win'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================= COVERAGE ============================= --}}
    <section class="py-5" style="background:var(--soft);">
        <div class="container py-3">
            <div class="text-center mb-5">
                <span class="chip"><i class="fas fa-map-location-dot"></i> {{ __('welcome.coverage_badge') }}</span>
                <h2 class="fw-bold display-6 mt-3">{{ __('welcome.coverage_title') }}</h2>
            </div>
            @php $covMeta = [['fa-city','g-green'],['fa-road','g-blue'],['fa-tree','g-amber']]; @endphp
            <div class="row g-4">
                @foreach(__('welcome.coverage') as $i => $c)
                    <div class="col-md-4">
                        <div class="tile text-center">
                            <div class="ic {{ $covMeta[$i][1] ?? 'g-green' }} mx-auto"><i class="fas {{ $covMeta[$i][0] ?? 'fa-city' }}"></i></div>
                            <h4 class="fw-bold">{{ $c['t'] }}</h4>
                            <p class="text-muted mb-0">{{ $c['d'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================= STEPS ============================= --}}
    <section class="py-5" style="background:var(--soft);">
        <div class="container py-3">
            <div class="text-center mb-5">
                <span class="chip"><i class="fas fa-wand-magic-sparkles"></i> {{ __('welcome.steps_badge') }}</span>
                <h2 class="fw-bold display-6 mt-3">{{ __('welcome.steps_title') }}</h2>
            </div>
            @php $stColors = ['grad','g-blue','g-amber']; @endphp
            <div class="row g-4">
                @foreach(__('welcome.steps') as $i => $st)
                    <div class="col-md-4 step text-center">
                        <div class="n {{ $stColors[$i] ?? 'grad' }}">{{ $i + 1 }}</div>
                        <h5 class="fw-bold">{{ $st['t'] }}</h5>
                        <p class="text-muted mb-0">{{ $st['d'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================= BENEFITS ============================= --}}
    <section class="py-5 bg-white">
        <div class="container py-3">
            <div class="text-center mb-5">
                <span class="chip"><i class="fas fa-star"></i> {{ __('welcome.benefits_badge') }}</span>
                <h2 class="fw-bold display-6 mt-3">{{ __('welcome.benefits_title') }}</h2>
            </div>
            @php $benMeta = [['fa-wallet','g-green'],['fa-clock','g-blue'],['fa-leaf','g-amber'],['fa-shield-halved','g-rose']]; @endphp
            <div class="row g-4">
                @foreach(__('welcome.benefits') as $i => $b)
                    <div class="col-md-3 col-sm-6"><div class="tile"><div class="ic {{ $benMeta[$i][1] ?? 'g-green' }}"><i class="fas {{ $benMeta[$i][0] ?? 'fa-star' }}"></i></div><h5 class="fw-bold">{{ $b['t'] }}</h5><p class="text-muted mb-0">{{ $b['d'] }}</p></div></div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================= APP SCREENSHOTS (telefon ichida) ============================= --}}
    <section class="py-5 bg-white">
        <div class="container py-3">
            <div class="row align-items-center g-5">
                <div class="col-lg-5 reveal">
                    <span class="chip"><i class="fas fa-mobile-screen-button"></i> {{ __('welcome.shots_badge') }}</span>
                    <h2 class="fw-bold display-6 mt-3">{{ __('welcome.shots_title') }}</h2>
                    <p class="text-muted">{{ __('welcome.shots_sub') }}</p>
                    @php $featIcons = ['fa-magnifying-glass','fa-calendar-check','fa-credit-card','fa-star']; @endphp
                    <div class="mt-3">
                        @foreach(__('welcome.shots_feat') as $i => $f)
                            <div class="shot-feat"><i class="fas {{ $featIcons[$i] ?? 'fa-check' }}"></i> <span>{{ $f }}</span></div>
                        @endforeach
                    </div>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="#" class="store" style="box-shadow:0 12px 28px rgba(11,19,36,.18);"><i class="fab fa-apple"></i>
                            <span class="t"><small>{{ __('welcome.download') }}</small><strong>App Store</strong></span></a>
                        <a href="#" class="store" style="box-shadow:0 12px 28px rgba(11,19,36,.18);"><i class="fab fa-google-play"></i>
                            <span class="t"><small>{{ __('welcome.download') }}</small><strong>Google Play</strong></span></a>
                    </div>
                </div>

                <div class="col-lg-7 reveal" style="transition-delay:120ms;">
                    @php
                        // Papkadagi haqiqiy ekran rasmlarini avtomatik oladi (16, 18 — qancha bo'lsa).
                        $shotFiles = glob(public_path('image/landing/landing-*.jpg')) ?: [];
                        sort($shotFiles);
                        $shots = array_map('basename', $shotFiles);
                        if (empty($shots)) { $shots = [null]; } // rasm umuman bo'lmasa, fallback
                    @endphp
                    <div class="device">
                        <div class="device-screen">
                            <div id="appShots" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="2400" data-bs-pause="false">
                                <div class="carousel-inner">
                                    @foreach($shots as $i => $shot)
                                        <div class="carousel-item @if($i === 0) active @endif">
                                            <img src="{{ $shot ? asset('image/landing/' . $shot) : asset('image/default.jpg') }}"
                                                 alt="ketamiz.com ilova ekrani {{ $i + 1 }}" loading="lazy"
                                                 onerror="this.onerror=null; this.src='{{ asset('image/default.jpg') }}';">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Indikator nuqtalar --}}
                    <div class="dots">
                        @foreach($shots as $i => $shot)
                            <button type="button" data-bs-target="#appShots" data-bs-slide-to="{{ $i }}"
                                    class="@if($i === 0) active @endif" aria-label="Ekran {{ $i + 1 }}"></button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================= DOWNLOAD ============================= --}}
    <section class="py-5" style="background:var(--soft);">
        <div class="container py-3">
            <div class="dl text-center">
                <span class="chip chip-d"><i class="fas fa-mobile-screen-button"></i> {{ __('welcome.download_badge') }}</span>
                <h2 class="fw-bold display-5 mt-3 mb-3">ketamiz<span class="grad-text">.com</span> {{ __('welcome.download_title') }}</h2>
                <p class="fs-5 mx-auto mb-4" style="max-width:620px;color:#aab4c8;">{{ __('welcome.download_sub') }}</p>
                <div class="d-flex justify-content-center flex-wrap gap-3">
                    <a href="https://apps.apple.com/pl/app/ketamiz/id6782315775" class="store"><i class="fab fa-apple"></i>
                        <span class="t"><small>{{ __('welcome.download') }}</small><strong>App Store</strong></span></a>
                    <a href="https://play.google.com/store/apps/details?id=uz.ketamiz.app" class="store"><i class="fab fa-google-play"></i>
                        <span class="t"><small>{{ __('welcome.download') }}</small><strong>Google Play</strong></span></a>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================= FOOTER ============================= --}}
    <footer class="ft">
        <div class="container">
            <div class="row gy-4 align-items-center">
                <div class="col-md-5">
                    <div class="wm fs-3 text-white mb-2">ketamiz<span class="dot">.com</span></div>
                    <p class="mb-0 small">{{ __('welcome.footer_tagline') }}</p>
                </div>
                <div class="col-md-4">
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('login') }}">{{ __('welcome.login') }}</a>
                        @if (Route::has('register'))<a href="{{ route('register') }}">{{ __('welcome.register') }}</a>@endif
                        <a href="#">{{ __('welcome.app') }}</a>
                    </div>
                </div>
                <div class="col-md-3 text-md-end">
                    <div class="d-flex gap-3 justify-content-md-end fs-5">
                        <a href="#" aria-label="Telegram"><i class="fab fa-telegram"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color:rgba(255,255,255,.08);">
            <div class="d-flex flex-wrap justify-content-between gap-2 small">
                <span>&copy; {{ date('Y') }} ketamiz.com — {{ __('welcome.rights') }}</span>
                <span>Powered by <span class="grad-text fw-bold">GOTOGETHER</span></span>
            </div>
        </div>
    </footer>

    {{-- Scroll-reveal animatsiya: elementlar ko‘rinish maydoniga kirganda yumshoq paydo bo‘ladi --}}
    <script>
        (function () {
            var els = document.querySelectorAll('.ketamiz .reveal');
            if (!('IntersectionObserver' in window)) {
                els.forEach(function (e) { e.classList.add('show'); });
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (en.isIntersecting) {
                        en.target.classList.add('show');
                        io.unobserve(en.target);
                    }
                });
            }, { threshold: 0.12 });
            els.forEach(function (e) { io.observe(e); });
        })();
    </script>
@endguest

</div>
@endsection
