@extends('layouts.app')

@section('title', 'Drivers List')

@section('content')
<div class="container py-4">
    <h1 class="text-center mb-4">🚖 {{__("Haydavchilar ")}}</h1>

    {{-- Search
    <div class="row mb-4">
        <div class="col-md-6 offset-md-3">
            <form action="{{ route('drivers.index') }}" method="GET">
                <div class="input-group shadow-sm rounded">
                    <input type="text" name="search" class="form-control" 
                           placeholder="🔍 Driver qidiring(telefon,ism yoki familyasi orqali)" 
                           value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Qidirish
                    </button>
                </div>
            </form>
        </div>
    </div> --}}

    <div class="row mb-3">
        <div class="col-md-12 d-flex flex-wrap align-items-center">
            <form action="{{ route('drivers.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                <input type="text" name="search" class="form-control me-2 mb-2" placeholder="🔍 Haydovchi qidiring..." value="{{ $search }}">
                <div class="btn-group me-2 mb-2" role="group">
                    <button type="submit" name="status" value="" class="btn btn-outline-secondary {{ $status == '' ? 'active' : '' }}">Barchasi</button>
                    <button type="submit" name="status" value="none" class="btn btn-outline-dark {{ $status == 'none' ? 'active' : '' }}">None</button>
                    <button type="submit" name="status" value="pending" class="btn btn-outline-warning {{ $status == 'pending' ? 'active' : '' }}">Pending</button>
                    <button type="submit" name="status" value="approved" class="btn btn-outline-success {{ $status == 'approved' ? 'active' : '' }}">Approved</button>
                    <button type="submit" name="status" value="rejected" class="btn btn-outline-danger {{ $status == 'rejected' ? 'active' : '' }}">Rejected</button>
                    <button type="submit" name="status" value="blocked" class="btn btn-outline-dark {{ $status == 'blocked' ? 'active' : '' }}">Blocked</button>
                </div>
            </form>
        </div>
    </div>
    
    

    {{-- Drivers Table --}}
    <div class="table-responsive shadow rounded">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Ismi</th>
                    <th>Telefon</th>
                    <th>Ro'li</th>
                    <th>Sms orqali tasdiqlanish</th>
                    <th>Hozirgi holati </th>
                    
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                    <tr class="text-center">
                        <td>{{ $driver->id }}</td>
                        <td>{{ $driver->first_name }}</td>
                        <td>{{ $driver->phone }}</td>
                        <td>{{ ucfirst($driver->role) }}</td>
                        <td class="text-center">
                            @if($driver->is_verified)
                                <span class="badge bg-success">Tasdiqlangan</span>
                            @else
                                <span class="badge bg-danger">Tasdiqlanmagan</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @php
                                $statusColors = [
                                    'none' => 'bg-secondary',
                                    'pending' => 'bg-warning text-dark',
                                    'approved' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    'blocked' => 'bg-dark text-white'
                                ];
                                $status = $driver->driving_verification_status ?? 'none';
                                $badgeClass = $statusColors[$status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                        
                        <td>
                            <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>

                           {{-- Pagination --}}
            @if($drivers->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $drivers->links('pagination::bootstrap-5') }}
            </div>
            @endif
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No drivers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $drivers->links() }}
    </div>
</div>

{{-- FontAwesome --}}
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
@endsection
