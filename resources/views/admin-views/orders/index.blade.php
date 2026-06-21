@extends('layouts.app')

@section('title', 'Orders List')

@push('styles')
<style>
    .k-page { max-width: 1250px; }
    .k-hero {
        background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
        color: #fff; border-radius: 20px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 24px 50px -24px rgba(14,165,233,.6);
    }
    .k-hero h1 { font-size: 1.6rem; margin: 0; color: #fff; }

    .k-card { background: #fff; border: 1px solid #eef2f7; border-radius: 18px; box-shadow: 0 18px 40px -28px rgba(11,19,36,.45); }
    .k-card .k-card-body { padding: 1.25rem; }
    .k-card table { margin: 0; }
    .k-card thead th { background: var(--k-ink); color: #fff; font-weight: 600; font-size: .8rem; border: 0; white-space: nowrap; }
    .k-card tbody tr { transition: background .15s; }
    .k-card tbody tr:hover { background: #f8fafc; }
    .k-card td small { font-size: .78rem; }
</style>
@endpush

@section('content')
<div class="container k-page py-4">

    {{-- Hero --}}
    <div class="k-hero d-flex align-items-center gap-3 mb-4">
        <div class="me-auto">
            <h1><i class="fas fa-box-open me-2"></i> Buyurtmalar</h1>
            <div class="mt-1 opacity-75">
                Jami: {{ $bookings->total() }} ta ·
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y H:i') }}
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="k-card mb-4">
        <div class="k-card-body">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-2">
                <select name="status" class="form-select w-auto">
                    <option value="">Barcha holatlar</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="confirmed" {{ request('status')=='confirmed'?'selected':'' }}>Confirmed</option>
                    <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Cancelled</option>
                    <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
                </select>

                <select name="date" class="form-select w-auto">
                    <option value="">Barcha sanalar</option>
                    <option value="today" {{ request('date')=='today'?'selected':'' }}>Bugun</option>
                    <option value="week" {{ request('date')=='week'?'selected':'' }}>Shu hafta</option>
                    <option value="last_week" {{ request('date')=='last_week'?'selected':'' }}>O'tgan hafta</option>
                </select>

                <button type="submit" class="btn btn-primary rounded-3"><i class="fas fa-filter me-1"></i> Filtrlash</button>
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary rounded-3">Tozalash</a>
            </form>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="k-card mb-4">
        <div class="k-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-start">Trip</th>
                            <th class="text-start">Mijoz</th>
                            <th class="text-start">Haydovchi</th>
                            <th>O'rin</th>
                            <th>Summa</th>
                            <th>O'rin narxi</th>
                            <th>Holat</th>
                            <th>Boshlanish / Tugash</th>
                            <th>Yaratilgan / Yangilangan</th>
                            <th>Amal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking->id }}</td>
                            <td class="text-start">
                                @if($booking->trip)
                                    <strong>{{ $booking->trip->startQuarter->name ?? 'N/A' }} → {{ $booking->trip->endQuarter->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $booking->trip->vehicle->model ?? 'Moshina N/A' }} · {{ $booking->trip->vehicle->car_number ?? '' }}</small>
                                @else
                                    <span class="text-muted">Trip yo‘q</span>
                                @endif
                            </td>
                            <td class="text-start">
                                {{ $booking->user->first_name ?? '' }} {{ $booking->user->last_name ?? '' }}<br>
                                <small class="text-muted">{{ $booking->user->phone ?? '-' }}</small>
                            </td>
                            <td class="text-start">
                                {{ $booking->trip->driver->first_name ?? '' }} {{ $booking->trip->driver->last_name ?? '' }}<br>
                                <small class="text-muted">{{ $booking->trip->driver->phone ?? '-' }}</small>
                            </td>
                            <td>{{ $booking->seats_booked }}</td>
                            <td>{{ number_format($booking->total_price, 0, '.', ' ') }} so'm</td>
                            <td>{{ number_format($booking->trip->price_per_seat ?? 0, 0, '.', ' ') }} so'm</td>
                            <td>
                                @php
                                    $bs = [
                                        'pending' => 'bg-secondary',
                                        'confirmed' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                        'completed' => 'bg-info',
                                        'expired' => 'bg-dark',
                                    ];
                                @endphp
                                <span class="badge {{ $bs[$booking->status] ?? 'bg-secondary' }} rounded-pill px-3 py-2">{{ ucfirst($booking->status) }}</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $booking->trip ? \Carbon\Carbon::parse($booking->trip->start_time)->format('d.m.Y H:i') : '-' }}</small><br>
                                <small class="text-muted">{{ $booking->trip ? \Carbon\Carbon::parse($booking->trip->end_time)->format('d.m.Y H:i') : '-' }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $booking->created_at->format('d.m.Y H:i') }}</small><br>
                                <small class="text-muted">{{ $booking->updated_at->format('d.m.Y H:i') }}</small>
                            </td>
                            <td>
                                <a href="{{ route('orders.show', $booking->id) }}" class="btn btn-sm btn-primary rounded-3"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-muted py-4">Buyurtmalar topilmadi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if($bookings->hasPages())
        <div class="d-flex justify-content-center">
            {{ $bookings->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>
@endsection
