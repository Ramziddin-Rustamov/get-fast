@extends('layouts.app')

@section('title', 'Client Bookings')

@push('styles')
<style>
    .k-page { max-width: 1100px; }

    .k-hero {
        background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
        color: #fff; border-radius: 20px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 24px 50px -24px rgba(14,165,233,.6);
    }
    .k-hero h1 { font-size: 1.5rem; margin: 0; color: #fff; }

    /* Trip card */
    .trip-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 40px -28px rgba(11,19,36,.45);
    }
    .trip-card-head {
        display: flex; justify-content: space-between; align-items: flex-start;
        gap: 1rem; flex-wrap: wrap;
        padding: 1.1rem 1.25rem;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f7;
    }
    .trip-route {
        display: flex; align-items: center; gap: .6rem;
        font-family: 'Sora', sans-serif; font-weight: 700; font-size: 1.05rem;
    }
    .trip-route .arrow { color: var(--k-acc-2); }
    .trip-meta { display: flex; flex-wrap: wrap; gap: 1.25rem; margin-top: .5rem; color: #64748b; font-size: .9rem; }
    .trip-meta b { color: var(--k-ink); }

    .trip-card-body { padding: 1.25rem; }

    /* Booking block */
    .booking-box { border: 1px solid #eef2f7; border-radius: 14px; padding: 1rem; }
    .booking-tags { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .35rem; }
    .booking-tags .pill {
        background: #f1f5f9; border-radius: 999px; padding: .25rem .7rem;
        font-size: .82rem; font-weight: 600; color: #334155;
    }

    .pass-table { margin: 0; }
    .pass-table thead th { background: var(--k-ink); color: #fff; font-weight: 600; font-size: .8rem; border: 0; }
    .pass-table td, .pass-table th { vertical-align: middle; }

    .sec-label { font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; font-weight: 700; margin: .25rem 0 .6rem; }
</style>
@endpush

@section('content')
<div class="container k-page py-4">

    @if(session('success'))
        <div class="alert alert-success rounded-4 border-0 shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-4 border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    {{-- Hero --}}
    <div class="k-hero d-flex align-items-center gap-3 mb-4">
        <div class="me-auto">
            <h1><i class="fas fa-calendar-check me-2"></i> {{ $client->first_name }} {{ $client->last_name }} — Buyurtmalari</h1>
            <div class="mt-1 opacity-75">Jami: {{ $trips->total() }} ta buyurtma</div>
        </div>
        <a href="{{ route('clients.show', $client->id) }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-arrow-left me-1"></i> Mijozga qaytish
        </a>
    </div>

    @forelse($trips as $booking)
        @php $trip = $booking->trip; @endphp
        <div class="trip-card mb-4">

            {{-- Header --}}
            <div class="trip-card-head">
                <div>
                    <div class="trip-route">
                        <i class="fas fa-location-dot text-success"></i>
                        {{ $trip->startQuarter->name ?? 'N/A' }}
                        <span class="arrow"><i class="fas fa-arrow-right-long"></i></span>
                        {{ $trip->endQuarter->name ?? 'N/A' }}
                        <i class="fas fa-flag-checkered text-danger"></i>
                    </div>
                    <div class="trip-meta">
                        <span><i class="fas fa-map-marker-alt me-1"></i>
                            {{ $trip->startQuarter->district->name_uz ?? '' }}, {{ $trip->startQuarter->district->region->name_uz ?? '' }}
                            →
                            {{ $trip->endQuarter->district->name_uz ?? '' }}, {{ $trip->endQuarter->district->region->name_uz ?? '' }}
                        </span>
                    </div>
                    <div class="trip-meta">
                        <span><i class="far fa-clock me-1"></i>
                            {{ \Carbon\Carbon::parse($trip->start_time)->format('d.m.Y H:i') }} —
                            {{ \Carbon\Carbon::parse($trip->end_time)->format('d.m.Y H:i') }}
                        </span>
                        <span><b>{{ number_format($trip->price_per_seat, 0, '.', ' ') }}</b> so'm/o'rin</span>
                    </div>
                </div>

                <span class="badge {{ $booking->status === 'expired' ? 'bg-danger' : ($booking->status === 'cancelled' ? 'bg-secondary' : 'bg-success') }} px-3 py-2 rounded-pill">
                    #{{ $booking->id }} · {{ ucfirst($booking->status) }}
                </span>
            </div>

            {{-- Body --}}
            <div class="trip-card-body">
                <div class="booking-box">
                    <div class="booking-tags">
                        <span class="pill"><i class="fas fa-chair me-1"></i>{{ $booking->seats_booked }} o'rin</span>
                        <span class="pill"><i class="fas fa-coins me-1"></i>{{ number_format($booking->total_price, 0, '.', ' ') }} so'm</span>
                        <span class="pill"><i class="fas fa-users me-1"></i>{{ $booking->passengers->count() }} passenger</span>
                        <span class="pill"><i class="far fa-clock me-1"></i>{{ $booking->created_at->format('d.m.Y H:i') }}</span>
                    </div>

                    {{-- Passengers --}}
                    @if ($booking->passengers->count())
                        <div class="table-responsive mt-3">
                            <table class="table pass-table table-sm align-middle">
                                <thead class="text-center">
                                    <tr>
                                        <th>#</th>
                                        <th>Ism</th>
                                        <th>Telefon</th>
                                        <th>Status</th>
                                        <th>Uy manzili</th>
                                        <th>Amal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($booking->passengers as $i => $passenger)
                                        <tr class="text-center">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $passenger->name }}</td>
                                            <td>{{ $passenger->phone }}</td>
                                            <td>
                                                <span class="badge {{ $passenger->status == 'cancelled' ? 'bg-danger' : ($passenger->status == 'confirmed' ? 'bg-success' : 'bg-warning text-dark') }}">
                                                    {{ ucfirst($passenger->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($passenger->latitude && $passenger->longitude)
                                                    <a href="https://www.google.com/maps?q={{ $passenger->latitude }},{{ $passenger->longitude }}"
                                                       target="_blank" class="btn btn-sm btn-outline-primary rounded-3">
                                                        <i class="fas fa-location-dot me-1"></i> Xaritada
                                                    </a>
                                                @else
                                                    <span class="text-muted">Manzil yo‘q</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($passenger->status == 'cancelled')
                                                    <span class="text-muted small">Bekor qilingan</span>
                                                @else
                                                    <form action="{{ route('drivers.passenger.cancel', [$booking->id, $passenger->id]) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Bu yo‘lovchini bekor qilasizmi? Pul clientga qaytariladi va haydovchidan yechib olinadi.')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger rounded-3">
                                                            <i class="fas fa-xmark me-1"></i> Bekor qilish
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted small mb-0 mt-2">Yo‘lovchi qo‘shilmagan.</p>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="trip-card"><div class="trip-card-body"><p class="text-muted mb-0">Hozircha buyurtmalar mavjud emas.</p></div></div>
    @endforelse

    @if($trips->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $trips->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>
@endsection
