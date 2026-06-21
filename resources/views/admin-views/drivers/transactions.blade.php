@extends('layouts.app')

@section('title', 'Driver Transactions')

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

    .k-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 18px;
        box-shadow: 0 18px 40px -28px rgba(11,19,36,.45);
    }
    .k-card .k-card-head {
        display: flex; align-items: center; justify-content: space-between;
        gap: .75rem; flex-wrap: wrap;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .k-card .k-card-body { padding: 1.25rem; }
    .k-title { display: flex; align-items: center; gap: .6rem; font-size: 1.05rem; font-weight: 700; margin: 0; }
    .k-chip {
        width: 38px; height: 38px; border-radius: 11px;
        display: grid; place-items: center; color: #fff; font-size: .95rem;
        background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
    }
    .k-card thead th { background: var(--k-ink); color: #fff; font-weight: 600; font-size: .82rem; border: 0; }
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
            <h1><i class="fas fa-money-bill-transfer me-2"></i> {{ $driver->first_name }} {{ $driver->last_name }} — Pul harakatlari</h1>
            <div class="mt-1 opacity-75">Jami: {{ $balanceTransactions->total() }} ta yozuv</div>
        </div>
        <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-arrow-left me-1"></i> Haydovchiga qaytish
        </a>
    </div>

    <div class="k-card mb-4">
        <div class="k-card-head">
            <h2 class="k-title"><span class="k-chip"><i class="fas fa-money-bill-transfer"></i></span> Pul harakatlari</h2>
        </div>
        <div class="k-card-body">
            @if($balanceTransactions->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="text-center">
                            <tr>
                                <th>#</th>
                                <th>Tur</th>
                                <th>Summa</th>
                                <th>Balans oldin</th>
                                <th>Balans keyin</th>
                                <th>Trip</th>
                                <th>Holat</th>
                                <th>Sabab / Izoh</th>
                                <th>Sana</th>
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
                                                data-end-long="{{ $t->endPoint->long ?? '' }}">
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

                    {{-- Boshlanish nuqtasi --}}
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="fw-bold text-success mb-1"><i class="fas fa-location-dot me-1"></i> Boshlanish</div>
                        <div id="tm-from" class="mb-2"></div>
                        <div class="small text-muted" id="tm-start-coord"></div>
                        <a id="tm-start-map" href="#" target="_blank" class="btn btn-sm btn-outline-success rounded-3 mt-2">
                            <i class="fas fa-map-location-dot me-1"></i> Xaritada ko‘rish
                        </a>
                    </div>

                    {{-- Tugash nuqtasi --}}
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
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Vaqt</span>
                            <strong id="tm-time" class="text-end"></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Narx</span>
                            <strong id="tm-price" class="text-end"></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">O'rinlar</span>
                            <strong id="tm-seats" class="text-end"></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Holat</span>
                            <strong id="tm-status" class="text-end"></strong>
                        </li>
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
                dir.classList.remove('disabled');
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
