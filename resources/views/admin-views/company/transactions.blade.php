@extends('layouts.app')

@section('title', 'Company Transactions')

@push('styles')
<style>
    .k-page { max-width: 1200px; }
    .k-hero {
        background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
        color: #fff; border-radius: 20px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 24px 50px -24px rgba(14,165,233,.6);
    }
    .k-hero h1 { font-size: 1.6rem; margin: 0; color: #fff; }

    .k-card { background: #fff; border: 1px solid #eef2f7; border-radius: 18px; box-shadow: 0 18px 40px -28px rgba(11,19,36,.45); }
    .k-card table { margin: 0; }
    .k-card thead th { background: var(--k-ink); color: #fff; font-weight: 600; font-size: .82rem; border: 0; white-space: nowrap; }
    .k-card tbody tr { transition: background .15s; }
    .k-card tbody tr:hover { background: #f8fafc; }
    .reason-cell { max-width: 320px; white-space: normal; font-size: .85rem; color: #475569; }
</style>
@endpush

@section('content')
<div class="container k-page py-4">

    {{-- Hero --}}
    <div class="k-hero d-flex align-items-center gap-3 mb-4">
        <div class="me-auto">
            <h1><i class="fas fa-receipt me-2"></i> Company Tranzaksiyalari</h1>
            <div class="mt-1 opacity-75">Jami: {{ $transactions->total() }} ta yozuv</div>
        </div>
        <a href="{{ route('company.dashboard') }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <div class="k-card mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>Summa</th>
                        <th>Balans oldin</th>
                        <th>Balans keyin</th>
                        <th class="text-start">Trip</th>
                        <th class="text-start">Mijoz</th>
                        <th>Tur</th>
                        <th class="text-start">Sabab</th>
                        <th>Sana</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $t)
                        @php $isIn = in_array($t->type, ['income', 'incoming']); @endphp
                        <tr class="text-center">
                            <td>{{ $t->id }}</td>
                            <td class="fw-bold {{ $isIn ? 'text-success' : 'text-danger' }}">
                                {{ $isIn ? '+' : '−' }}{{ number_format($t->amount, 0, '.', ' ') }} so'm
                            </td>
                            <td>{{ number_format($t->balance_before, 0, '.', ' ') }}</td>
                            <td>{{ number_format($t->balance_after, 0, '.', ' ') }}</td>
                            <td class="text-start">
                                @if($t->trip)
                                    @php
                                        $tr = $t->trip;
                                        $sq = $tr->startQuarter->name ?? '';
                                        $eq = $tr->endQuarter->name ?? '';
                                        $sd = $tr->startDistrict->name_uz ?? ($tr->startDistrict->name ?? '');
                                        $ed = $tr->endDistrict->name_uz ?? ($tr->endDistrict->name ?? '');
                                        $sr = $tr->startRegion->name_uz ?? ($tr->startRegion->name ?? '');
                                        $er = $tr->endRegion->name_uz ?? ($tr->endRegion->name ?? '');
                                        $from = implode(', ', array_filter([$sq, $sd, $sr]));
                                        $to   = implode(', ', array_filter([$eq, $ed, $er]));
                                    @endphp
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-3 trip-btn"
                                            data-bs-toggle="modal" data-bs-target="#tripModal"
                                            data-from="{{ $from }}"
                                            data-to="{{ $to }}"
                                            data-time="{{ \Carbon\Carbon::parse($tr->start_time)->format('d.m.Y H:i') }} — {{ \Carbon\Carbon::parse($tr->end_time)->format('d.m.Y H:i') }}"
                                            data-price="{{ number_format($tr->price_per_seat, 0, '.', ' ') }} so'm"
                                            data-seats="{{ $tr->available_seats }} / {{ $tr->total_seats }}"
                                            data-status="{{ ucfirst($tr->status) }}"
                                            data-start-lat="{{ $tr->startPoint->lat ?? '' }}"
                                            data-start-long="{{ $tr->startPoint->long ?? '' }}"
                                            data-end-lat="{{ $tr->endPoint->lat ?? '' }}"
                                            data-end-long="{{ $tr->endPoint->long ?? '' }}">
                                        <i class="fas fa-route me-1"></i> {{ $sq ?: 'N/A' }} → {{ $eq ?: 'N/A' }}
                                    </button>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-start">
                                @if($t->booking?->user)
                                    <a href="{{ route('clients.show', $t->booking->user->id) }}"
                                       class="text-decoration-none fw-semibold">
                                        <i class="fas fa-user text-muted me-1"></i>
                                        {{ $t->booking->user->first_name }} {{ $t->booking->user->last_name }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $isIn ? 'bg-success' : 'bg-danger' }} rounded-pill px-3 py-2">
                                    {{ $isIn ? 'Kirim' : 'Chiqim' }}
                                </span>
                            </td>
                            <td class="reason-cell">{{ $t->reason }}</td>
                            <td>{{ \Carbon\Carbon::parse($t->created_at)->format('d.m.Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Tranzaksiyalar mavjud emas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
        <div class="d-flex justify-content-center">
            {{ $transactions->links('pagination::bootstrap-5') }}
        </div>
    @endif

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
