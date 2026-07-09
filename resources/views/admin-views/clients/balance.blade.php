@extends('layouts.app')

@section('title', 'Client Transactions')

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
    .k-card { background: #fff; border: 1px solid #eef2f7; border-radius: 18px; box-shadow: 0 18px 40px -28px rgba(11,19,36,.45); }
    .k-card .k-card-body { padding: 1.25rem; }
    .k-card thead th { background: var(--k-ink); color: #fff; font-weight: 600; font-size: .82rem; border: 0; }
</style>
@endpush

@section('content')
<div class="container k-page py-4">

    {{-- Hero --}}
    <div class="k-hero d-flex align-items-center gap-3 mb-4">
        <div class="me-auto">
            <h1><i class="fas fa-money-bill-transfer me-2"></i> {{ $client->first_name }} {{ $client->last_name }} — Pul harakatlari</h1>
            <div class="mt-1 opacity-75">
                Jami: {{ $balanceTransactions->total() }} ta yozuv ·
                Balans: {{ number_format($client->balance?->balance ?? 0, 2, '.', ' ') }} so'm
            </div>
        </div>
        <a href="{{ route('clients.show', $client->id) }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-arrow-left me-1"></i> Mijozga qaytish
        </a>
    </div>

    <div class="k-card mb-4">
        <div class="k-card-body">
            @if($balanceTransactions->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="text-center">
                            <tr>
                                <th>#</th><th>Tur</th><th>Summa</th><th>Balans oldin</th><th>Balans keyin</th><th>Trip</th><th>Holat</th><th>Sabab / Izoh</th><th>Sana</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($balanceTransactions as $transaction)
                            <tr class="text-center">
                                <td>{{ $transaction->id }}</td>
                                <td>
                                    <span class="badge {{ $transaction->type === 'debit' ? 'bg-danger' : 'bg-success' }}">
                                        {{ $transaction->type === 'debit' ? 'Chiqim' : 'Kirim' }}
                                    </span>
                                </td>
                                <td>So'm {{ number_format($transaction->amount, 2, '.', ' ') }}</td>
                                <td>So'm {{ number_format($transaction->balance_before, 2, '.', ' ') }}</td>
                                <td>So'm {{ number_format($transaction->balance_after, 2, '.', ' ') }}</td>
                                <td>
                                    @if($transaction->trip)
                                        @php
                                            $t  = $transaction->trip;
                                            $sq = $t->startQuarter->name ?? '';
                                            $eq = $t->endQuarter->name ?? '';
                                            $sd = $t->startDistrict->name_uz ?? ($t->startDistrict->name ?? '');
                                            $ed = $t->endDistrict->name_uz ?? ($t->endDistrict->name ?? '');
                                            $sr = $t->startRegion->name_uz ?? ($t->startRegion->name ?? '');
                                            $er = $t->endRegion->name_uz ?? ($t->endRegion->name ?? '');
                                            $from = implode(', ', array_filter([$sq, $sd, $sr]));
                                            $to   = implode(', ', array_filter([$eq, $ed, $er]));

                                            $parcelData = null;
                                            if ($t->parcel) {
                                                $parcelData = [
                                                    'is_active'        => (bool) $t->parcel->is_active,
                                                    'max_weight'       => $t->parcel->max_weight,
                                                    'available_weight' => $t->parcel->available_weight ?? $t->parcel->max_weight,
                                                    'price_per_kg'     => $t->parcel->price_per_kg,
                                                    'dims'             => ($t->parcel->max_length || $t->parcel->max_width || $t->parcel->max_height)
                                                        ? [$t->parcel->max_length, $t->parcel->max_width, $t->parcel->max_height] : null,
                                                    'types'            => $t->parcel->types->pluck('name_uz')->values(),
                                                    'bookings'         => $t->parcelBookings->map(function ($pb) {
                                                        return [
                                                            'sender'      => trim(($pb->user->first_name ?? '') . ' ' . ($pb->user->last_name ?? '')) ?: 'N/A',
                                                            'phone'       => $pb->user->phone ?? null,
                                                            'receiver'    => $pb->receiver_phone ?? null,
                                                            'type'        => $pb->type->name_uz ?? null,
                                                            'weight'      => $pb->weight,
                                                            'dims'        => ($pb->length || $pb->width || $pb->height) ? [$pb->length, $pb->width, $pb->height] : null,
                                                            'price'       => number_format($pb->total_price, 0, '.', ' '),
                                                            'status'      => $pb->status,
                                                            'description' => $pb->parcel_description ?? null,
                                                        ];
                                                    })->values(),
                                                ];
                                            }
                                        @endphp
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3 trip-btn"
                                                data-bs-toggle="modal" data-bs-target="#tripModal"
                                                data-from="{{ $from }}"
                                                data-to="{{ $to }}"
                                                data-time="{{ \Carbon\Carbon::parse($t->start_time)->format('d.m.Y H:i') }} — {{ \Carbon\Carbon::parse($t->end_time)->format('d.m.Y H:i') }}"
                                                data-price="{{ number_format($t->price_per_seat, 0, '.', ' ') }} so'm"
                                                data-seats="{{ $t->available_seats }} / {{ $t->total_seats }}"
                                                data-status="{{ ucfirst($t->status) }}"
                                                data-start-lat="{{ $t->startPoint->lat ?? '' }}"
                                                data-start-long="{{ $t->startPoint->long ?? '' }}"
                                                data-end-lat="{{ $t->endPoint->lat ?? '' }}"
                                                data-end-long="{{ $t->endPoint->long ?? '' }}"
                                                data-parcel="{{ $parcelData ? json_encode($parcelData, JSON_UNESCAPED_UNICODE) : '' }}">
                                            <i class="fas fa-route me-1"></i> {{ $sq ?: 'N/A' }} → {{ $eq ?: 'N/A' }}
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->status === 'success')
                                        <span class="badge bg-success">Muvaffaqiyatli</span>
                                    @elseif($transaction->status === 'pending')
                                        <span class="badge bg-warning text-dark">Kutilmoqda</span>
                                    @else
                                        <span class="badge bg-danger">Xato</span>
                                    @endif
                                </td>
                                <td>{{ $transaction->reason ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d.m.Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($balanceTransactions->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $balanceTransactions->links('pagination::bootstrap-5') }}
                </div>
                @endif
            @else
                <p class="text-muted text-center mb-0">Hozircha pul harakatlari mavjud emas.</p>
            @endif
        </div>
    </div>

    {{-- Trip detail popup --}}
    <div class="modal fade" id="tripModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title"><i class="fas fa-route me-2"></i>Trip ma'lumotlari</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    {{-- Boshlanish --}}
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="fw-bold text-success mb-1"><i class="fas fa-location-dot me-1"></i> Boshlanish</div>
                        <div id="tm-from" class="mb-2"></div>
                        <div class="small text-muted" id="tm-start-coord"></div>
                        <a id="tm-start-map" href="#" target="_blank" class="btn btn-sm btn-outline-success rounded-3 mt-2">
                            <i class="fas fa-map-location-dot me-1"></i> Xaritada ko‘rish
                        </a>
                    </div>

                    {{-- Tugash --}}
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="fw-bold text-danger mb-1"><i class="fas fa-flag-checkered me-1"></i> Tugash</div>
                        <div id="tm-to" class="mb-2"></div>
                        <div class="small text-muted" id="tm-end-coord"></div>
                        <a id="tm-end-map" href="#" target="_blank" class="btn btn-sm btn-outline-danger rounded-3 mt-2">
                            <i class="fas fa-map-location-dot me-1"></i> Xaritada ko‘rish
                        </a>
                    </div>

                    {{-- Yo'nalish --}}
                    <div class="text-center mb-3">
                        <a id="tm-dir-map" href="#" target="_blank" class="btn btn-primary rounded-3">
                            <i class="fas fa-route me-1"></i> Yo‘nalishni xaritada ko‘rish
                        </a>
                    </div>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Vaqt</span><strong id="tm-time" class="text-end"></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Narx</span><strong id="tm-price" class="text-end"></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">O'rinlar</span><strong id="tm-seats" class="text-end"></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Holat</span><strong id="tm-status" class="text-end"></strong></li>
                    </ul>

                    {{-- Pochta (parcel) --}}
                    <div class="mt-4">
                        <div class="fw-bold mb-2"><i class="fas fa-box me-1 text-primary"></i> Pochta</div>
                        <div id="tm-parcel"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.querySelectorAll('.trip-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const d = this.dataset;

            document.getElementById('tm-from').textContent   = d.from || 'N/A';
            document.getElementById('tm-to').textContent     = d.to || 'N/A';
            document.getElementById('tm-time').textContent   = d.time;
            document.getElementById('tm-price').textContent  = d.price;
            document.getElementById('tm-seats').textContent  = d.seats;
            document.getElementById('tm-status').textContent = d.status;

            renderParcel(d.parcel);

            setupPoint('tm-start-coord', 'tm-start-map', d.startLat, d.startLong);
            setupPoint('tm-end-coord',   'tm-end-map',   d.endLat,   d.endLong);

            const dir = document.getElementById('tm-dir-map');
            if (d.startLat && d.startLong && d.endLat && d.endLong) {
                dir.href = `https://www.google.com/maps/dir/?api=1&origin=${d.startLat},${d.startLong}&destination=${d.endLat},${d.endLong}`;
                dir.style.display = '';
            } else {
                dir.style.display = 'none';
            }
        });
    });

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
        });
    }

    function statusBadge(status) {
        var s = (status || '').toLowerCase();
        var cls = (s === 'cancelled' || s === 'rejected') ? 'bg-danger'
                : (s === 'confirmed' ? 'bg-success' : 'bg-warning text-dark');
        var label = status ? status.charAt(0).toUpperCase() + status.slice(1) : '—';
        return '<span class="badge ' + cls + '">' + esc(label) + '</span>';
    }

    function renderParcel(raw) {
        var box = document.getElementById('tm-parcel');
        if (!box) return;

        if (!raw) {
            box.innerHTML = '<div class="text-muted small">Bu trip pochta qabul qilmaydi.</div>';
            return;
        }

        var p;
        try { p = JSON.parse(raw); } catch (e) { box.innerHTML = ''; return; }

        var pills = [];
        if (!p.is_active) {
            pills.push('<span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Nofaol</span>');
        }
        pills.push('<span class="badge bg-light text-dark border"><i class="fas fa-weight-hanging me-1"></i>Maks ' + esc(p.max_weight ?? '—') + ' kg</span>');
        pills.push('<span class="badge" style="background:#dcfce7;color:#166534;"><i class="fas fa-box-open me-1"></i>Bo\'sh: ' + esc(p.available_weight ?? '—') + ' kg</span>');
        pills.push('<span class="badge bg-light text-dark border"><i class="fas fa-coins me-1"></i>' + esc(p.price_per_kg ?? 0) + ' so\'m/kg</span>');
        if (p.dims) {
            pills.push('<span class="badge bg-light text-dark border"><i class="fas fa-ruler-combined me-1"></i>' + (p.dims[0] ?? '?') + '×' + (p.dims[1] ?? '?') + '×' + (p.dims[2] ?? '?') + ' sm</span>');
        }
        (p.types || []).forEach(function (t) {
            pills.push('<span class="badge" style="background:#e0f2fe;color:#0369a1;">' + esc(t) + '</span>');
        });

        var html = '<div class="d-flex flex-wrap gap-2 mb-2">' + pills.join('') + '</div>';

        var bookings = p.bookings || [];
        html += '<div class="fw-semibold small text-muted mb-2"><i class="fas fa-inbox me-1"></i> Posilkalar (' + bookings.length + ')</div>';

        if (!bookings.length) {
            html += '<div class="text-muted small">Bu tripga posilka yuborilmagan.</div>';
        } else {
            bookings.forEach(function (b) {
                var tags = [];
                if (b.type) tags.push('<span class="badge" style="background:#e0f2fe;color:#0369a1;">' + esc(b.type) + '</span>');
                tags.push('<span class="badge bg-light text-dark border"><i class="fas fa-weight-hanging me-1"></i>' + esc(b.weight) + ' kg</span>');
                if (b.dims) tags.push('<span class="badge bg-light text-dark border"><i class="fas fa-ruler-combined me-1"></i>' + (b.dims[0] ?? '?') + '×' + (b.dims[1] ?? '?') + '×' + (b.dims[2] ?? '?') + ' sm</span>');
                tags.push('<span class="badge bg-light text-dark border"><i class="fas fa-coins me-1"></i>' + esc(b.price) + ' so\'m</span>');

                html += '<div class="border rounded-3 p-2 mb-2">'
                     + '<div class="d-flex justify-content-between align-items-start gap-2">'
                     + '<div><div class="fw-semibold"><i class="fas fa-user me-1 text-muted"></i>' + esc(b.sender) + '</div>'
                     + (b.phone ? '<div class="text-muted small"><i class="fas fa-phone me-1"></i>' + esc(b.phone) + '</div>' : '')
                     + (b.receiver ? '<div class="text-muted small"><i class="fas fa-user-check me-1"></i>Qabul qiluvchi: ' + esc(b.receiver) + '</div>' : '')
                     + '</div>' + statusBadge(b.status) + '</div>'
                     + '<div class="d-flex flex-wrap gap-2 mt-2">' + tags.join('') + '</div>'
                     + (b.description ? '<div class="text-muted small mt-2"><i class="fas fa-note-sticky me-1"></i>' + esc(b.description) + '</div>' : '')
                     + '</div>';
            });
        }

        box.innerHTML = html;
    }

    function setupPoint(coordId, mapId, lat, long) {
        const coordEl = document.getElementById(coordId);
        const mapEl   = document.getElementById(mapId);
        if (lat && long) {
            coordEl.textContent = `Lat: ${lat}, Long: ${long}`;
            mapEl.href = `https://www.google.com/maps?q=${lat},${long}`;
            mapEl.style.display = '';
        } else {
            coordEl.textContent = 'Koordinata yo‘q';
            mapEl.style.display = 'none';
        }
    }
</script>
@endsection
