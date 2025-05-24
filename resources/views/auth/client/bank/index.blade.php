@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary"><i class="fas fa-credit-card"></i> {{ __('My Credit Cards') }}</h3>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Credit Card List --}}
    <div class="row">
        @if ($cards->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-credit-card fa-2x mb-2"></i>
                <p>{{ __('You have no credit cards yet.') }}</p>
            </div>
        @endif

        @foreach ($cards as $card)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm position-relative">
                    <div class="card-body bg-light rounded">
                        <h5 class="card-title text-dark">
                            <i class="fas fa-credit-card me-2"></i>•••• •••• •••• {{ substr($card->card_number, -4) }}
                        </h5>
                        <p class="text-muted mb-2">
                            <strong>Exp:</strong> {{ str_pad($card->expiry_month, 2, '0', STR_PAD_LEFT) }}/{{ $card->expiry_year }} <br>
                            <strong>CVV:</strong> •••
                        </p>
                        <div class="d-flex justify-content-between mt-3">
                            <form action="{{ route('client.banks.destroy', $card->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash-alt"></i> {{ __('Delete') }}
                                </button>
                            </form>
                            {{-- Future Edit Button --}}
                            {{-- <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editCardModal{{ $card->id }}">
                                <i class="fas fa-edit"></i> Edit
                            </button> --}}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Add New Card --}}
    <div class="card mt-5 shadow border-0">
        <div class="card-header bg-gradient bg-primary text-white">
            <i class="fas fa-plus-circle me-1"></i> {{ __('Add New Card') }}
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('client.banks.store') }}">
                @csrf
                @include('auth.client.bank.form')
                <div class="mt-3">
                    <button class="btn btn-success w-100">
                        <i class="fas fa-check-circle me-1"></i> {{ __('Add Card') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
