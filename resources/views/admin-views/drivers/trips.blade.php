@extends('layouts.app')

@section('title', 'Driver Trips')

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
    .booking-box { border: 1px solid #eef2f7; border-radius: 14px; padding: 1rem; margin-bottom: 1rem; }
    .booking-box:last-child { margin-bottom: 0; }
    .booking-user { font-weight: 700; }
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
            <h1><i class="fas fa-route me-2"></i> {{ $driver->first_name }} {{ $driver->last_name }} — Triplari</h1>
            <div class="mt-1 opacity-75">Jami: {{ $driver->driverTrips->count() }} ta trip</div>
        </div>
        <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-arrow-left me-1"></i> Haydovchiga qaytish
        </a>
    </div>

    @forelse ($driver->driverTrips->sortByDesc('created_at') as $trip)
        <div class="trip-card mb-4">

            {{-- Header --}}
            <div class="trip-card-head">
                <div>
                    @php $lc = app()->getLocale(); @endphp
                    <div class="trip-route">
                        <i class="fas fa-location-dot text-success"></i>
                        <div>
                            <div>{{ $trip->startQuarter->name ?? 'N/A' }}</div>
                            <div class="text-muted small fw-normal">
                                {{ $trip->startRegion->{'name_' . $lc} ?? $trip->startRegion->name_uz ?? '—' }},
                                {{ $trip->startDistrict->{'name_' . $lc} ?? $trip->startDistrict->name_uz ?? '—' }}
                            </div>
                        </div>
                        <span class="arrow"><i class="fas fa-arrow-right-long"></i></span>
                        <div>
                            <div>{{ $trip->endQuarter->name ?? 'N/A' }}</div>
                            <div class="text-muted small fw-normal">
                                {{ $trip->endRegion->{'name_' . $lc} ?? $trip->endRegion->name_uz ?? '—' }},
                                {{ $trip->endDistrict->{'name_' . $lc} ?? $trip->endDistrict->name_uz ?? '—' }}
                            </div>
                        </div>
                        <i class="fas fa-flag-checkered text-danger"></i>
                    </div>
                    <div class="trip-meta">
                        <span><i class="far fa-clock me-1"></i>
                            {{ \Carbon\Carbon::parse($trip->start_time)->format('d.m.Y H:i') }} —
                            {{ \Carbon\Carbon::parse($trip->end_time)->format('d.m.Y H:i') }}
                        </span>
                        <span><b>{{ number_format($trip->price_per_seat, 0, '.', ' ') }}</b> so'm</span>
                        <span><b>{{ $trip->available_seats }}</b> / {{ $trip->total_seats }} o'rin</span>
                    </div>
                </div>

                <div class="text-end">
                    <span class="badge {{ $trip->status === 'cancelled' ? 'bg-danger' : 'bg-success' }} px-3 py-2 rounded-pill">
                        {{ ucfirst($trip->status) }}
                    </span>

                    @if($trip->status !== 'cancelled' && $trip->status !== 'completed')
                        <form action="{{ route('drivers.trip.cancel', $trip->id) }}" method="POST" class="mt-2"
                              onsubmit="return confirm('Safarni admin tomonidan bekor qilasizmi? Mijozlarga to‘liq summa + kompensatsiya qaytariladi, haydovchidan xizmat haqqi ushlanadi.')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger rounded-3">
                                <i class="fas fa-ban me-1"></i> Safarni bekor qilish
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Body: bookings --}}
            <div class="trip-card-body">
                <p class="sec-label"><i class="fas fa-ticket me-1"></i> Bookings ({{ $trip->bookings->count() }})</p>

                @forelse ($trip->bookings as $booking)
                    <div class="booking-box">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <div class="booking-user">
                                    <i class="fas fa-user me-1 text-muted"></i>
                                    {{ $booking->user->first_name ?? 'N/A' }} {{ $booking->user->last_name ?? '' }}
                                </div>
                                <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $booking->user->phone ?? 'N/A' }}</div>
                            </div>
                            <span class="badge {{ $booking->status == 'cancelled' ? 'bg-danger' : 'bg-primary' }} rounded-pill px-3 py-2">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>

                        <div class="booking-tags">
                            <span class="pill"><i class="fas fa-chair me-1"></i>{{ $booking->seats_booked }} o'rin</span>
                            <span class="pill"><i class="fas fa-coins me-1"></i>{{ number_format($booking->total_price, 0, '.', ' ') }} so'm</span>
                            <span class="pill"><i class="fas fa-users me-1"></i>{{ $booking->passengers->count() }} passenger</span>
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
                            <p class="text-muted small mb-0 mt-2">Passengerlar qo‘shilmagan.</p>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">Bu tripda booking yo‘q.</p>
                @endforelse

                {{-- Pochta (parcel) --}}
                <hr class="my-4">
                <p class="sec-label"><i class="fas fa-box me-1"></i> Pochta</p>

                @if($trip->parcel)
                    {{-- Haydovchi qabul qiladigan shartlar --}}
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <div class="booking-tags">
                            @unless($trip->parcel->is_active)
                                <span class="pill" style="background:#fee2e2;color:#b91c1c;"><i class="fas fa-ban me-1"></i>Nofaol</span>
                            @endunless
                            <span class="pill"><i class="fas fa-weight-hanging me-1"></i>Maks {{ $trip->parcel->max_weight ?? '—' }} kg</span>
                            <span class="pill" style="background:#dcfce7;color:#166534;"><i class="fas fa-box-open me-1"></i>Bo'sh: {{ $trip->parcel->available_weight ?? $trip->parcel->max_weight ?? '—' }} kg</span>
                            <span class="pill"><i class="fas fa-coins me-1"></i>{{ number_format($trip->parcel->price_per_kg ?? 0, 0, '.', ' ') }} so'm/kg</span>
                            @if($trip->parcel->max_length || $trip->parcel->max_width || $trip->parcel->max_height)
                                <span class="pill"><i class="fas fa-ruler-combined me-1"></i>{{ $trip->parcel->max_length ?? '?' }}×{{ $trip->parcel->max_width ?? '?' }}×{{ $trip->parcel->max_height ?? '?' }} sm</span>
                            @endif
                            @foreach($trip->parcel->types as $type)
                                <span class="pill" style="background:#e0f2fe;color:#0369a1;">{{ $type->name_uz }}</span>
                            @endforeach
                        </div>

                        @if($trip->status !== 'cancelled' && $trip->status !== 'completed')
                            @if($trip->parcel->is_active)
                                <form action="{{ route('drivers.trip.parcel.disable', $trip->id) }}" method="POST"
                                      onsubmit="return confirm('Pochta qabul qilishni o‘chirasizmi? Mavjud posilkalar bekor qilinadi (bazadan o‘chmaydi).')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">
                                        <i class="fas fa-box-open me-1"></i> Pochta olishni o‘chirish
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('drivers.trip.parcel.enable', $trip->id) }}" method="POST"
                                      onsubmit="return confirm('Pochta qabul qilishni qayta yoqasizmi?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-3">
                                        <i class="fas fa-box me-1"></i> Qayta yoqish
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>

                    {{-- Kelgan posilkalar --}}
                    <p class="sec-label"><i class="fas fa-inbox me-1"></i> Posilkalar ({{ $trip->parcelBookings->count() }})</p>

                    @forelse($trip->parcelBookings as $pb)
                        <div class="booking-box">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <div class="booking-user">
                                        <i class="fas fa-user me-1 text-muted"></i>
                                        {{ $pb->user->first_name ?? 'N/A' }} {{ $pb->user->last_name ?? '' }}
                                    </div>
                                    <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $pb->user->phone ?? 'N/A' }}</div>
                                    <div class="text-muted small"><i class="fas fa-user-check me-1"></i>Qabul qiluvchi: {{ $pb->receiver_phone ?? '—' }}</div>
                                </div>
                                <span class="badge {{ $pb->status == 'cancelled' || $pb->status == 'rejected' ? 'bg-danger' : ($pb->status == 'confirmed' ? 'bg-success' : 'bg-warning text-dark') }} rounded-pill px-3 py-2">
                                    {{ ucfirst($pb->status) }}
                                </span>
                            </div>

                            <div class="booking-tags">
                                @if($pb->type)
                                    <span class="pill" style="background:#e0f2fe;color:#0369a1;">{{ $pb->type->name_uz }}</span>
                                @endif
                                <span class="pill"><i class="fas fa-weight-hanging me-1"></i>{{ $pb->weight }} kg</span>
                                @if($pb->length || $pb->width || $pb->height)
                                    <span class="pill"><i class="fas fa-ruler-combined me-1"></i>{{ $pb->length ?? '?' }}×{{ $pb->width ?? '?' }}×{{ $pb->height ?? '?' }} sm</span>
                                @endif
                                <span class="pill"><i class="fas fa-coins me-1"></i>{{ number_format($pb->total_price, 0, '.', ' ') }} so'm</span>
                            </div>

                            @if($pb->parcel_description)
                                <div class="text-muted small mt-2"><i class="fas fa-note-sticky me-1"></i>{{ $pb->parcel_description }}</div>
                            @endif

                            {{-- Admin bekor qilish: ikki taraf ham zarar ko'rmaydi --}}
                            @if(in_array($pb->status, ['pending', 'confirmed']))
                                <div class="mt-3 d-flex justify-content-end">
                                    <form action="{{ route('drivers.parcel.cancel', $pb->id) }}" method="POST"
                                          onsubmit="return confirm('Posilkani bekor qilasizmi? Mijozga to‘liq summa qaytariladi, haydovchidan olgan daromadi yechiladi — ikki taraf ham zarar ko‘rmaydi.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger rounded-3">
                                            <i class="fas fa-xmark me-1"></i> Posilkani bekor qilish
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="mt-2 text-muted small text-end">
                                    <i class="fas fa-circle-info me-1"></i>{{ ucfirst($pb->status) }} — bekor qilib bo‘lmaydi
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Bu tripga posilka yuborilmagan.</p>
                    @endforelse
                @else
                    <p class="text-muted mb-0">Bu trip pochta qabul qilmaydi.</p>
                @endif
            </div>
        </div>
    @empty
        <div class="trip-card"><div class="trip-card-body"><p class="text-muted mb-0">Triplar mavjud emas.</p></div></div>
    @endforelse

</div>
@endsection
