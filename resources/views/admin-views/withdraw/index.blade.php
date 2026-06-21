@extends('layouts.app')

@section('title', 'Withdraw Requests')

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

    .k-card { background: #fff; border: 1px solid #eef2f7; border-radius: 18px; box-shadow: 0 18px 40px -28px rgba(11,19,36,.45); }
    .k-card .k-card-body { padding: 1.25rem; }
    .k-card table { margin: 0; }
    .k-card thead th { background: var(--k-ink); color: #fff; font-weight: 600; font-size: .85rem; border: 0; white-space: nowrap; }
    .k-card tbody tr { transition: background .15s; }
    .k-card tbody tr:hover { background: #f8fafc; }

    .k-avatar {
        width: 38px; height: 38px; border-radius: 11px;
        display: grid; place-items: center; font-weight: 700; color: #fff; font-family: 'Sora', sans-serif;
        background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
    }
    .card-num { font-family: 'Sora', monospace; font-weight: 600; letter-spacing: .03em; }
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
            <h1><i class="fas fa-money-bill-transfer me-2"></i> Pul yechish so‘rovlari</h1>
            <div class="mt-1 opacity-75">Jami: {{ $withdraws->total() }} ta so‘rov</div>
        </div>
    </div>

    <div class="k-card mb-4">
        <div class="k-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="text-center">
                        <tr>
                            <th>#</th>
                            <th class="text-start">Foydalanuvchi</th>
                            <th>Balans</th>
                            <th>Rol</th>
                            <th>Summa</th>
                            <th class="text-start">Karta</th>
                            <th>Holat</th>
                            <th>Amal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($withdraws as $w)
                            <tr class="text-center">
                                <td>{{ $w->id }}</td>
                                <td class="text-start">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="k-avatar">{{ strtoupper(mb_substr($w->user->first_name ?? '?', 0, 1)) }}</div>
                                        <div>
                                            <div class="fw-semibold">{{ $w->user->first_name ?? '—' }} {{ $w->user->last_name ?? '' }}</div>
                                            <small class="text-muted">{{ $w->user->phone ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ number_format($w->user->balance?->balance ?? 0, 0, '.', ' ') }} so'm</td>
                                <td><span class="badge bg-secondary text-uppercase rounded-pill px-3 py-2">{{ $w->role }}</span></td>
                                <td><strong>{{ number_format($w->amount, 0, '.', ' ') }} so'm</strong></td>
                                <td class="text-start">
                                    <div class="card-num">{{ $w->card?->number ?? '—' }}</div>
                                    <small class="text-muted">{{ $w->card_holder }}</small>
                                </td>
                                <td>
                                    @if($w->status == 'pending')
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Kutilmoqda</span>
                                    @elseif($w->status == 'approved')
                                        <span class="badge bg-success rounded-pill px-3 py-2">Tasdiqlangan</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-3 py-2">Rad etilgan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($w->status == 'pending')
                                        <div class="d-flex justify-content-center gap-1">
                                            <form action="{{ route('admin.withdraw.approve', $w->id) }}" method="POST"
                                                  onsubmit="return confirm('Tasdiqlaysizmi?')">
                                                @csrf
                                                <button class="btn btn-success btn-sm rounded-3"><i class="fas fa-check me-1"></i> Tasdiq</button>
                                            </form>
                                            <form action="{{ route('admin.withdraw.reject', $w->id) }}" method="POST"
                                                  onsubmit="return confirm('Rad etilsinmi?')">
                                                @csrf
                                                <button class="btn btn-danger btn-sm rounded-3"><i class="fas fa-xmark me-1"></i> Rad</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">So‘rovlar mavjud emas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if($withdraws->hasPages())
        <div class="d-flex justify-content-center">
            {{ $withdraws->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>
@endsection
