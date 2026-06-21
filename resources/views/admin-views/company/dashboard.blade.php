@extends('layouts.app')

@section('title', 'Company Dashboard')

@push('styles')
<style>
    .k-page { max-width: 1150px; }
    .k-hero {
        background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
        color: #fff; border-radius: 20px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 24px 50px -24px rgba(14,165,233,.6);
    }
    .k-hero h1 { font-size: 1.6rem; margin: 0; color: #fff; }

    .sec-label { font-family: 'Sora', sans-serif; font-weight: 700; font-size: 1.05rem; margin: 1.75rem 0 .9rem; display: flex; align-items: center; gap: .5rem; }
    .sec-label .bar { width: 4px; height: 20px; border-radius: 4px; background: linear-gradient(var(--k-acc-1), var(--k-acc-2)); }

    .stat {
        background: #fff; border: 1px solid #eef2f7; border-radius: 16px;
        padding: 1.1rem 1.25rem; height: 100%;
        box-shadow: 0 18px 40px -28px rgba(11,19,36,.45);
        display: flex; align-items: center; gap: .9rem;
    }
    .stat .ico { width: 46px; height: 46px; border-radius: 13px; display: grid; place-items: center; font-size: 1.15rem; color: #fff; flex: none; }
    .stat .lbl { font-size: .8rem; color: #64748b; font-weight: 600; margin: 0; }
    .stat .num { font-family: 'Sora', sans-serif; font-weight: 800; font-size: 1.5rem; line-height: 1.1; margin: 0; color: var(--k-ink); }

    .bg-grad   { background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2)); }
    .bg-green  { background: #16a34a; }
    .bg-blue   { background: #2563eb; }
    .bg-amber  { background: #f59e0b; }
    .bg-red    { background: #dc2626; }
    .bg-cyan   { background: #0891b2; }
    .bg-dark2  { background: #1e293b; }
</style>
@endpush

@section('content')
<div class="container k-page py-4">

    {{-- Hero --}}
    <div class="k-hero d-flex align-items-center gap-3 mb-2">
        <div class="me-auto">
            <h1><i class="fas fa-gauge-high me-2"></i> Company Dashboard</h1>
            <div class="mt-1 opacity-75">Umumiy ko‘rsatkichlar va statistika</div>
        </div>
        <a href="{{ route('company.transactions') }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-receipt me-1"></i> Tranzaksiyalar
        </a>
    </div>

    {{-- Balance --}}
    <div class="sec-label"><span class="bar"></span> Balans</div>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="stat">
                <div class="ico bg-grad"><i class="fas fa-wallet"></i></div>
                <div><p class="lbl">Joriy balans</p><p class="num">{{ number_format($company->balance ?? 0, 0, '.', ' ') }} <small class="text-muted fs-6">so'm</small></p></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat">
                <div class="ico bg-blue"><i class="fas fa-arrow-trend-up"></i></div>
                <div><p class="lbl">Umumiy daromad</p><p class="num">{{ number_format($company->total_income ?? 0, 0, '.', ' ') }} <small class="text-muted fs-6">so'm</small></p></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat">
                <div class="ico bg-amber"><i class="fas fa-calendar-day"></i></div>
                <div><p class="lbl">Bugungi daromad</p><p class="num">{{ number_format($todayIncome ?? 0, 0, '.', ' ') }} <small class="text-muted fs-6">so'm</small></p></div>
            </div>
        </div>
    </div>

    {{-- Bookings --}}
    <div class="sec-label"><span class="bar"></span> Buyurtmalar statistikasi</div>
    <div class="row g-3">
        @php
            $bookingStats = [
                ['title' => 'Jami buyurtma', 'count' => $totalBookings, 'ico' => 'fa-list', 'bg' => 'bg-blue'],
                ['title' => 'Tasdiqlangan', 'count' => $confirmedBookings, 'ico' => 'fa-circle-check', 'bg' => 'bg-green'],
                ['title' => 'Bekor qilingan', 'count' => $cancelledBookings, 'ico' => 'fa-circle-xmark', 'bg' => 'bg-red'],
                ['title' => 'Yakunlangan', 'count' => $completedBookings, 'ico' => 'fa-flag-checkered', 'bg' => 'bg-cyan'],
            ];
        @endphp
        @foreach ($bookingStats as $b)
        <div class="col-md-3 col-6">
            <div class="stat">
                <div class="ico {{ $b['bg'] }}"><i class="fas {{ $b['ico'] }}"></i></div>
                <div><p class="lbl">{{ $b['title'] }}</p><p class="num">{{ $b['count'] }}</p></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Users --}}
    <div class="sec-label"><span class="bar"></span> Foydalanuvchilar</div>
    <div class="row g-3">
        @php
            $userStats = [
                ['title' => 'Mijozlar', 'count' => $totalClients, 'ico' => 'fa-users', 'bg' => 'bg-cyan'],
                ['title' => 'Haydovchilar', 'count' => $totalDrivers, 'ico' => 'fa-id-card', 'bg' => 'bg-amber'],
                ['title' => 'Faol', 'count' => $activeUsers, 'ico' => 'fa-user-check', 'bg' => 'bg-green'],
                ['title' => 'Nofaol', 'count' => $inactiveUsers, 'ico' => 'fa-user-slash', 'bg' => 'bg-red'],
            ];
        @endphp
        @foreach ($userStats as $u)
        <div class="col-md-3 col-6">
            <div class="stat">
                <div class="ico {{ $u['bg'] }}"><i class="fas {{ $u['ico'] }}"></i></div>
                <div><p class="lbl">{{ $u['title'] }}</p><p class="num">{{ $u['count'] }}</p></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Driver verification --}}
    <div class="sec-label"><span class="bar"></span> Haydovchi tasdiqlanish holati</div>
    <div class="row g-3">
        @php
            $driverStats = [
                ['title' => 'Approved', 'count' => $driversApproved, 'ico' => 'fa-circle-check', 'bg' => 'bg-green'],
                ['title' => 'Rejected', 'count' => $driversRejected, 'ico' => 'fa-circle-xmark', 'bg' => 'bg-red'],
                ['title' => 'Pending', 'count' => $driversPending, 'ico' => 'fa-clock', 'bg' => 'bg-amber'],
                ['title' => 'Blocked', 'count' => $driversBlocked, 'ico' => 'fa-ban', 'bg' => 'bg-dark2'],
            ];
        @endphp
        @foreach ($driverStats as $d)
        <div class="col-md-3 col-6">
            <div class="stat">
                <div class="ico {{ $d['bg'] }}"><i class="fas {{ $d['ico'] }}"></i></div>
                <div><p class="lbl">{{ $d['title'] }}</p><p class="num">{{ $d['count'] }}</p></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Cards --}}
    <div class="sec-label"><span class="bar"></span> Kartalar</div>
    <div class="row g-3">
        <div class="col-md-3 col-6">
            <div class="stat">
                <div class="ico bg-grad"><i class="fas fa-credit-card"></i></div>
                <div><p class="lbl">Jami kartalar</p><p class="num">{{ $totalCards }}</p></div>
            </div>
        </div>
    </div>

</div>
@endsection
