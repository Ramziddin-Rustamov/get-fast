@extends('layouts.app')

@section('title', 'Drivers List')

@push('styles')
<style>
    .k-page { max-width: 1100px; }

    .k-hero {
        background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
        color: #fff; border-radius: 20px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 24px 50px -24px rgba(14,165,233,.6);
    }
    .k-hero h1 { font-size: 1.6rem; margin: 0; color: #fff; }

    .k-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 18px;
        box-shadow: 0 18px 40px -28px rgba(11,19,36,.45);
    }
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

    .filter-btn.active { color: #fff; }
    .search-input { max-width: 320px; }
</style>
@endpush

@section('content')
<div class="container k-page py-4">

    {{-- Hero --}}
    <div class="k-hero d-flex align-items-center gap-3 mb-4">
        <div class="me-auto">
            <h1><i class="fas fa-id-card me-2"></i> {{ __('Haydovchilar') }}</h1>
            <div class="mt-1 opacity-75">Jami: {{ $drivers->total() }} ta haydovchi</div>
        </div>
    </div>

    {{-- Search & filters --}}
    <div class="k-card mb-4">
        <div class="k-card-body">
            <form action="{{ route('drivers.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group search-input">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0"
                           placeholder="Ism, familya yoki telefon..." value="{{ $search }}">
                </div>

                <div class="btn-group flex-wrap" role="group">
                    <button type="submit" name="status" value="" class="btn btn-outline-secondary filter-btn {{ $status == '' ? 'active' : '' }}">Barchasi</button>
                    <button type="submit" name="status" value="none" class="btn btn-outline-dark filter-btn {{ $status == 'none' ? 'active' : '' }}">None</button>
                    <button type="submit" name="status" value="pending" class="btn btn-outline-warning filter-btn {{ $status == 'pending' ? 'active' : '' }}">Pending</button>
                    <button type="submit" name="status" value="approved" class="btn btn-outline-success filter-btn {{ $status == 'approved' ? 'active' : '' }}">Approved</button>
                    <button type="submit" name="status" value="rejected" class="btn btn-outline-danger filter-btn {{ $status == 'rejected' ? 'active' : '' }}">Rejected</button>
                    <button type="submit" name="status" value="blocked" class="btn btn-outline-dark filter-btn {{ $status == 'blocked' ? 'active' : '' }}">Blocked</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Drivers Table --}}
    <div class="k-card mb-4">
        <div class="k-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="text-center">
                        <tr>
                            <th>#</th>
                            <th class="text-start">Haydovchi</th>
                            <th>Telefon</th>
                            <th>Ro'li</th>
                            <th>SMS tasdiqlanish</th>
                            <th>Hozirgi holati</th>
                            <th>Amal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $driver)
                            <tr class="text-center">
                                <td>{{ $driver->id }}</td>
                                <td class="text-start">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="k-avatar">{{ strtoupper(mb_substr($driver->first_name ?? '?', 0, 1)) }}</div>
                                        <div class="fw-semibold">{{ $driver->first_name }} {{ $driver->last_name }}</div>
                                    </div>
                                </td>
                                <td>{{ $driver->phone }}</td>
                                <td><span class="badge bg-primary rounded-pill px-3 py-2">{{ ucfirst($driver->role) }}</span></td>
                                <td>
                                    @if($driver->is_verified)
                                        <span class="badge bg-success rounded-pill px-3 py-2">Tasdiqlangan</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-3 py-2">Tasdiqlanmagan</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'none' => 'bg-secondary',
                                            'pending' => 'bg-warning text-dark',
                                            'approved' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'blocked' => 'bg-dark text-white',
                                        ];
                                        $dStatus = $driver->driving_verification_status ?? 'none';
                                        $badgeClass = $statusColors[$dStatus] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill">{{ ucfirst($dStatus) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-sm btn-primary rounded-3">
                                        <i class="fas fa-eye me-1"></i> Ko‘rish
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Haydovchi topilmadi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if($drivers->hasPages())
        <div class="d-flex justify-content-center">
            {{ $drivers->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>
@endsection
