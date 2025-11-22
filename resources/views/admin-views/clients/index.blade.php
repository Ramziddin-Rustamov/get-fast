@extends('layouts.app')

@section('title', 'Clients List')

@section('content')
<div class="container py-4">
    <h1 class="text-center mb-4">👤 {{ __("Mijozlar") }}</h1>

    {{-- Search & Filters --}}
    <div class="row mb-3">
        <div class="col-md-12 d-flex flex-wrap align-items-center">
            <form action="{{ route('clients.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                <input type="text" name="search" class="form-control me-2 mb-2"
                       placeholder="🔍 Client qidiring..." value="{{ $search }}">

                <div class="btn-group me-2 mb-2" role="group">
                    <button type="submit" name="status" value="" class="btn btn-outline-secondary {{ $status == '' ? 'active' : '' }}">
                        Barchasi
                    </button>
                    <button type="submit" name="status" value="0" class="btn btn-outline-dark {{ $status != '0' ? 'active' : '' }}">
                        Tasdiqlanmagan
                    </button>
                    <button type="submit" name="status" value="1" class="btn btn-outline-warning {{ $status == '1' ? 'active' : '' }}">
                        Tasdiqlangan
                    </button>
                  
                </div>
            </form>

            <a href="{{ route('clients.create') }}" class="btn btn-success ms-auto mb-2">
                <i class="fas fa-plus"></i> Yangi Client qo‘shish
            </a>
        </div>
    </div>

    {{-- Clients Table --}}
    <div class="table-responsive shadow rounded">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Ismi</th>
                    <th>Telefon</th>
                    <th>Ro‘li</th>
                    <th>Sms orqali tasdiqlanish</th>
                    <th>Holati</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($clients as $client)
                    <tr class="text-center">
                        <td>{{ $client->id }}</td>
                        <td>{{ $client->first_name }}</td>
                        <td>{{ $client->phone }}</td>

                        <td>{{ ucfirst($client->role) }}</td>

                        <td>
                            @if($client->is_verified)
                                <span class="badge bg-success">Tasdiqlangan</span>
                            @else
                                <span class="badge bg-danger">Tasdiqlanmagan</span>
                            @endif
                        </td>

                        <td>
                            @php
                                $statusColors = [
                                    'none' => 'bg-secondary',
                                    'pending' => 'bg-warning text-dark',
                                    'approved' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    'blocked' => 'bg-dark text-white'
                                ];
                                $statusValue = $client->verification_status ?? 'none';
                                $badgeClass = $statusColors[$statusValue] ?? 'bg-secondary';
                            @endphp

                            <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill">
                                {{ ucfirst($statusValue) }}
                            </span>
                        </td>

                        <td>
                            <a href="{{ route('clients.show', $client->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Clientlar topilmadi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $clients->links('pagination::bootstrap-5') }}
    </div>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
@endsection
