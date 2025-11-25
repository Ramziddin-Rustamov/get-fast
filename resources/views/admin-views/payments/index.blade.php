@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="container py-4">

    <h2 class="fw-bold mb-4">
        <i class="fas fa-money-check-alt me-2"></i> Payments Overview
    </h2>

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-0">

            <table class="table table-hover align-middle mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>#</th>
                        <th><i class="fas fa-user"></i> User</th>
                        <th><i class="fas fa-credit-card"></i> Card</th>
                        <th><i class="fas fa-coins"></i> Amount</th>
                        <th><i class="fas fa-check-circle"></i> Status</th>
                        <th><i class="fas fa-wallet"></i> Method</th>
                        <th><i class="fas fa-clock"></i> Date</th>
                        <th><i class="fas fa-eye"></i> View</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($payments as $p)
                    <tr class="table-row-hover">
                        <td class="fw-bold">#{{ $p->id }}</td>

                        {{-- USER --}}
                        <td>
                            <i class="fas fa-user-circle text-primary me-1"></i>
                            {{ $p->user->first_name ?? 'Unknown' }}
                        </td>

                        {{-- CARD --}}
                        <td>
                            <i class="far fa-credit-card text-success me-1"></i>
                            **** {{ substr($p->card->number ?? '----', -4) }}
                        </td>

                        {{-- TRIP --}}
                        {{-- AMOUNT --}}
                        <td class="fw-bold text-dark">
                            {{ number_format($p->amount) }} <small>UZS</small>
                        </td>

                        {{-- STATUS --}}
                        <td>
                            <span class="badge px-3 py-2 
                                @if($p->status === 'confirmed') bg-success
                                @elseif($p->status === 'created') bg-warning text-dark
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>

                        {{-- METHOD --}}
                        <td>
                            <i class="fas fa-university text-muted me-1"></i>
                            {{ $p->payment_method }}
                        </td>

                        {{-- DATE --}}
                        <td>
                            <i class="fas fa-calendar-alt text-muted me-1"></i>
                            {{ $p->created_at->format('d M Y H:i') }}
                        </td>

                        <td>
                            <a href="{{ route('payments.show', $p->id) }}"
                               class="btn btn-sm btn-primary rounded-pill">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

    <div class="mt-3">
        {{ $payments->links() }}
    </div>

</div>
@endsection
