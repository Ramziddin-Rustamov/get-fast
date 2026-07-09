@extends('layouts.app')

@section('title', 'Foydalanuvchi qidiruvlari')

@section('content')

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-lg-11">

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h3 class="fw-bold text-dark mb-0">🔍 Foydalanuvchi qidiruvlari</h3>
            </div>

            {{-- Statistika --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 text-center py-3">
                        <div class="fs-3 fw-bold text-primary">{{ number_format($stats['total']) }}</div>
                        <div class="text-muted small">Jami qidiruvlar</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 text-center py-3">
                        <div class="fs-3 fw-bold text-success">{{ number_format($stats['today']) }}</div>
                        <div class="text-muted small">Bugun</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 text-center py-3">
                        <div class="fs-3 fw-bold text-dark">{{ number_format($stats['registered']) }}</div>
                        <div class="text-muted small">Ro'yxatdan o'tgan foydalanuvchilar</div>
                    </div>
                </div>
            </div>

            {{-- Qidiruv --}}
            <form method="GET" action="{{ route('search-logs.index') }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="q" value="{{ $search }}" class="form-control rounded-start-pill"
                           placeholder="Manzil, ism yoki telefon bo'yicha qidirish...">
                    <button class="btn btn-primary rounded-end-pill px-4" type="submit">Qidirish</button>
                    @if($search !== '')
                        <a href="{{ route('search-logs.index') }}" class="btn btn-outline-secondary rounded-pill ms-2">Tozalash</a>
                    @endif
                </div>
            </form>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Foydalanuvchi</th>
                                    <th>Qayerdan</th>
                                    <th>Qayerga</th>
                                    <th>Ketish sanasi</th>
                                    <th class="text-center">Natija</th>
                                    <th class="pe-4">Qidirilgan vaqt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}"
                                        title="Batafsil ko'rish uchun bosing">
                                        <td class="ps-4 text-muted">{{ $log->id }}</td>
                                        <td>
                                            @if($log->user)
                                                <span class="fw-semibold">{{ $log->user->first_name }} {{ $log->user->last_name }}</span>
                                                <div class="text-muted small">{{ $log->user->phone }}</div>
                                            @else
                                                <span class="badge bg-secondary">Mehmon</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold">{{ $log->start_location ?? '—' }}</td>
                                        <td class="fw-semibold">
                                            {{ $log->end_location ?? '—' }}
                                            @if($log->is_round_trip)
                                                <span class="badge bg-info text-dark ms-1">↔️ Borib-kelish</span>
                                            @endif
                                        </td>
                                        <td>{{ $log->departure_date ? $log->departure_date->format('d.m.Y') : '—' }}</td>
                                        <td class="text-center">
                                            @if($log->results_count > 0)
                                                <span class="badge bg-success">{{ $log->results_count }} ta</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Topilmadi</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-muted small">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Hozircha qidiruvlar yo'q.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>

{{-- Har bir qidiruv uchun batafsil modal --}}
@foreach($logs as $log)
    <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">
                        🔍 Qidiruv #{{ $log->id }}
                        @if($log->is_round_trip)
                            <span class="badge bg-info text-dark ms-2">↔️ Borib-kelish</span>
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish"></button>
                </div>
                <div class="modal-body">

                    {{-- Foydalanuvchi --}}
                    <div class="mb-4">
                        <div class="text-muted small text-uppercase mb-1">Foydalanuvchi</div>
                        @if($log->user)
                            <div class="fw-semibold fs-5">{{ $log->user->first_name }} {{ $log->user->last_name }}</div>
                            <div class="text-muted">📞 {{ $log->user->phone }}</div>
                        @else
                            <span class="badge bg-secondary">Mehmon (ro'yxatdan o'tmagan)</span>
                        @endif
                    </div>

                    <div class="row g-3">
                        {{-- QAYERDAN --}}
                        <div class="col-md-6">
                            <div class="card border-0 bg-light rounded-4 h-100">
                                <div class="card-body">
                                    <div class="fw-bold text-success mb-3">📍 Qayerdan</div>
                                    <div class="mb-2">
                                        <div class="text-muted small">Viloyat</div>
                                        <div class="fw-semibold">{{ optional($log->startRegion)->name_uz ?? '—' }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="text-muted small">Tuman</div>
                                        <div class="fw-semibold">{{ optional($log->startDistrict)->name_uz ?? '—' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Mahalla / Qishloq</div>
                                        <div class="fw-semibold">{{ optional($log->startQuarter)->name ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- QAYERGA --}}
                        <div class="col-md-6">
                            <div class="card border-0 bg-light rounded-4 h-100">
                                <div class="card-body">
                                    <div class="fw-bold text-danger mb-3">🎯 Qayerga</div>
                                    <div class="mb-2">
                                        <div class="text-muted small">Viloyat</div>
                                        <div class="fw-semibold">{{ optional($log->endRegion)->name_uz ?? '—' }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="text-muted small">Tuman</div>
                                        <div class="fw-semibold">{{ optional($log->endDistrict)->name_uz ?? '—' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Mahalla / Qishloq</div>
                                        <div class="fw-semibold">{{ optional($log->endQuarter)->name ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Qo'shimcha ma'lumot --}}
                    <div class="row g-3 mt-1">
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Ketish sanasi</div>
                            <div class="fw-semibold">{{ $log->departure_date ? $log->departure_date->format('d.m.Y') : '—' }}</div>
                        </div>
                        @if($log->is_round_trip)
                            <div class="col-6 col-md-3">
                                <div class="text-muted small">Qaytish sanasi</div>
                                <div class="fw-semibold">{{ $log->return_date ? $log->return_date->format('d.m.Y') : '—' }}</div>
                            </div>
                        @endif
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Topilgan safarlar</div>
                            <div class="fw-semibold">
                                @if($log->results_count > 0)
                                    <span class="text-success">{{ $log->results_count }} ta</span>
                                @else
                                    <span class="text-warning">Topilmadi</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Qidirilgan vaqt</div>
                            <div class="fw-semibold">{{ $log->created_at->format('d.m.Y H:i') }}</div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Yopish</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
