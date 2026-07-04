@extends('layouts.app')

@section('title', 'Clients List')

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
    .k-avatar-img { width: 38px; height: 38px; border-radius: 11px; object-fit: cover; border: 1px solid #eef2f7; }
    .search-input { max-width: 320px; }
</style>
@endpush

@section('content')
<div class="container k-page py-4">

    {{-- Hero --}}
    <div class="k-hero d-flex align-items-center gap-3 mb-4">
        <div class="me-auto">
            <h1><i class="fas fa-users me-2"></i> {{ __('Mijozlar') }}</h1>
            <div class="mt-1 opacity-75">Jami: {{ $clients->total() }} ta mijoz</div>
        </div>
        <a href="{{ route('clients.create') }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-plus me-1"></i> Yangi mijoz
        </a>
    </div>

    {{-- Search & filters --}}
    <div class="k-card mb-4">
        <div class="k-card-body">
            <form action="{{ route('clients.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group search-input">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0"
                           placeholder="Ism, familya yoki telefon..." value="{{ $search ?? '' }}">
                </div>

                <div class="btn-group flex-wrap" role="group">
                    <button type="submit" name="status" value="" class="btn btn-outline-secondary {{ ($status ?? '') == '' ? 'active' : '' }}">Barchasi</button>
                    <button type="submit" name="status" value="0" class="btn btn-outline-dark {{ ($status ?? '') == '0' ? 'active' : '' }}">Tasdiqlanmagan</button>
                    <button type="submit" name="status" value="1" class="btn btn-outline-success {{ ($status ?? '') == '1' ? 'active' : '' }}">Tasdiqlangan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Clients Table --}}
    <div class="k-card mb-4">
        <div class="k-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="text-center">
                        <tr>
                            <th>#</th>
                            <th class="text-start">Mijoz</th>
                            <th>Telefon</th>
                            <th>Ro'li</th>
                            <th>Viloyat</th>
                            <th>Tuman</th>
                            <th>Mahalla</th>
                            <th>SMS tasdiqlanish</th>
                            <th>Holati</th>
                            <th>Amal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr class="text-center">
                                <td>{{ $client->id }}</td>
                                <td class="text-start">
                                    <div class="d-flex align-items-center gap-2">
                                        @php
                                            $img = $client->image;
                                            $src = ($img && $img !== 'default.jpg') ? asset('storage/' . $img) : asset('image/default.jpg');
                                        @endphp
                                        <img src="{{ $src }}" alt="" class="k-avatar-img avatar-zoom"
                                             style="cursor: zoom-in;"
                                             data-bs-toggle="modal" data-bs-target="#avatarModal"
                                             data-img="{{ $src }}"
                                             onerror="this.onerror=null;this.src='{{ asset('image/default.jpg') }}'">
                                        <div class="fw-semibold">{{ $client->first_name }} {{ $client->last_name }}</div>
                                    </div>
                                </td>
                                <td>{{ $client->phone }}</td>
                                <td><span class="badge bg-info rounded-pill px-3 py-2">{{ ucfirst($client->role) }}</span></td>
                                <td>{{ $client->region->name_uz ?? 'N/A' }}</td>
                                <td>{{ $client->district->name_uz ?? 'N/A' }}</td>
                                <td>{{ $client->quarter->name ?? 'N/A' }}</td>
                                <td>
                                    @if($client->is_verified)
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
                                        $statusValue = $client->verification_status ?? 'none';
                                        $badgeClass = $statusColors[$statusValue] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill">{{ ucfirst($statusValue) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('clients.show', $client->id) }}" class="btn btn-sm btn-primary rounded-3">
                                        <i class="fas fa-eye me-1"></i> Ko‘rish
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Mijozlar topilmadi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if($clients->hasPages())
        <div class="d-flex justify-content-center">
            {{ $clients->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    @endif

    {{-- Image preview Modal --}}
    <div class="modal fade" id="avatarModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 bg-transparent shadow-none">
                <div class="position-relative text-center">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2"
                            data-bs-dismiss="modal" style="z-index:2;"></button>
                    <img id="avatarModalImage" src="" class="img-fluid rounded-4 shadow" alt="">
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('click', function (e) {
        if (e.target.matches('.avatar-zoom')) {
            document.getElementById('avatarModalImage').src = e.target.dataset.img;
        }
    });
</script>
@endsection
