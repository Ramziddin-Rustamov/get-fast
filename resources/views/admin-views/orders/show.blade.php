@extends('layouts.app')

@section('title', 'Booking Details')

@push('styles')
<style>
    .k-page { max-width: 900px; }
    .k-hero {
        background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
        color: #fff; border-radius: 20px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 24px 50px -24px rgba(14,165,233,.6);
    }
    .k-hero h1 { font-size: 1.5rem; margin: 0; color: #fff; }

    .k-card { background: #fff; border: 1px solid #eef2f7; border-radius: 18px; box-shadow: 0 18px 40px -28px rgba(11,19,36,.45); overflow: hidden; }
    .k-card .k-card-head { padding: .9rem 1.25rem; border-bottom: 1px solid #f1f5f9; font-weight: 700; display: flex; align-items: center; gap: .6rem; }
    .k-card .k-card-body { padding: 1.25rem; }
    .k-chip { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; color: #fff; font-size: .9rem; }
    .info-table th { width: 40%; color: #64748b; font-weight: 600; }
    .info-table td, .info-table th { padding: .55rem .25rem; border-bottom: 1px solid #f1f5f9; }
    .k-card thead th { background: var(--k-ink); color: #fff; font-weight: 600; font-size: .82rem; border: 0; }
</style>
@endpush

@section('content')
<div class="container k-page py-4">

    {{-- Hero --}}
    <div class="k-hero d-flex align-items-center gap-3 mb-4">
        <div class="me-auto">
            <h1><i class="fas fa-receipt me-2"></i> Buyurtma #{{ $order->id }}</h1>
            <div class="mt-1 opacity-75">{{ number_format($order->total_price, 0, '.', ' ') }} so'm · {{ $order->seats_booked }} o'rin</div>
        </div>
        <a href="{{ route('orders.index') }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-arrow-left me-1"></i> Ro‘yxat
        </a>
    </div>

    @php
        $bs = ['pending' => 'bg-secondary', 'confirmed' => 'bg-success', 'cancelled' => 'bg-danger', 'completed' => 'bg-info', 'expired' => 'bg-dark'];
    @endphp

    {{-- Booking info --}}
    <div class="k-card mb-4">
        <div class="k-card-head"><span class="k-chip" style="background:var(--k-ink)"><i class="fas fa-circle-info"></i></span> Buyurtma ma'lumotlari</div>
        <div class="k-card-body">
            <table class="table info-table mb-0">
                <tr><th>ID</th><td>{{ $order->id }}</td></tr>
                <tr><th>Holat</th><td><span class="badge {{ $bs[$order->status] ?? 'bg-secondary' }} rounded-pill px-3 py-2">{{ ucfirst($order->status) }}</span></td></tr>
                <tr><th>Umumiy narx</th><td>{{ number_format($order->total_price, 0, '.', ' ') }} so'm</td></tr>
                <tr><th>Band qilingan o'rin</th><td>{{ $order->seats_booked }}</td></tr>
                <tr><th>Yaratilgan</th><td>{{ \Carbon\Carbon::parse($order->created_at)->format('d.m.Y H:i') }}</td></tr>
                <tr><th>Muddati</th><td>{{ $order->expired_at ? \Carbon\Carbon::parse($order->expired_at)->format('d.m.Y H:i') : '—' }}</td></tr>
                <tr><th>Jo'nab ketgan</th><td>{{ $order->departed_at ? \Carbon\Carbon::parse($order->departed_at)->format('d.m.Y H:i') : '—' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Client info --}}
    <div class="k-card mb-4">
        <div class="k-card-head"><span class="k-chip bg-primary"><i class="fas fa-user"></i></span> Mijoz ma'lumotlari</div>
        <div class="k-card-body">
            @if($order->user)
                <table class="table info-table mb-0">
                    <tr><th>To'liq ism</th><td>{{ $order->user->first_name ?? '' }} {{ $order->user->last_name ?? '' }}</td></tr>
                    <tr><th>Telefon</th><td>{{ $order->user->phone }}</td></tr>
                    <tr><th>Rol</th><td><span class="badge bg-info rounded-pill px-3 py-2">{{ ucfirst($order->user->role) }}</span></td></tr>
                </table>
            @else
                <p class="text-muted mb-0">Mijoz ma'lumoti yo‘q</p>
            @endif
        </div>
    </div>

    {{-- Trip info --}}
    <div class="k-card mb-4">
        <div class="k-card-head"><span class="k-chip bg-success"><i class="fas fa-route"></i></span> Trip ma'lumotlari</div>
        <div class="k-card-body">
            @if($order->trip)
                <table class="table info-table mb-0">
                    <tr><th>Trip ID</th><td>{{ $order->trip->id }}</td></tr>
                    <tr><th>Haydovchi</th><td>{{ $order->trip->driver->first_name ?? '' }} {{ $order->trip->driver->last_name ?? '' }} <small class="text-muted">{{ $order->trip->driver->phone ?? '' }}</small></td></tr>
                    <tr><th>Qayerdan</th><td>{{ $order->trip->startQuarter->name ?? 'N/A' }}</td></tr>
                    <tr><th>Qayerga</th><td>{{ $order->trip->endQuarter->name ?? 'N/A' }}</td></tr>
                    <tr><th>Boshlanish</th><td>{{ \Carbon\Carbon::parse($order->trip->start_time)->format('d.m.Y H:i') }}</td></tr>
                    <tr><th>Tugash</th><td>{{ \Carbon\Carbon::parse($order->trip->end_time)->format('d.m.Y H:i') }}</td></tr>
                </table>
            @else
                <p class="text-muted mb-0">Trip topilmadi</p>
            @endif
        </div>
    </div>

    {{-- Passengers --}}
    <div class="k-card mb-4">
        <div class="k-card-head"><span class="k-chip bg-info"><i class="fas fa-user-friends"></i></span> Yo‘lovchilar</div>
        <div class="k-card-body p-0">
            @if($order->passengers && count($order->passengers) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="text-center">
                            <tr><th>#</th><th>Ism</th><th>Telefon</th><th>Status</th><th>Uy manzili</th></tr>
                        </thead>
                        <tbody>
                            @foreach($order->passengers as $index => $p)
                                <tr class="text-center">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $p->name }}</td>
                                    <td>{{ $p->phone }}</td>
                                    <td>
                                        <span class="badge {{ $p->status == 'cancelled' ? 'bg-danger' : ($p->status == 'confirmed' ? 'bg-success' : 'bg-warning text-dark') }}">
                                            {{ ucfirst($p->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($p->latitude && $p->longitude)
                                            <a href="https://www.google.com/maps?q={{ $p->latitude }},{{ $p->longitude }}"
                                               target="_blank" class="btn btn-sm btn-outline-primary rounded-3">
                                                <i class="fas fa-location-dot me-1"></i> Xaritada
                                            </a>
                                        @else
                                            <span class="text-muted">Manzil yo‘q</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0 p-3">Yo‘lovchi yo‘q</p>
            @endif
        </div>
    </div>

</div>
@endsection
