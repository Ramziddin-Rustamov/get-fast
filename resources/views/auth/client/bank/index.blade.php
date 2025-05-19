@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2><i class="fas fa-credit-card"></i> My Credit Cards</h2>

    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger d-flex align-items-center">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Card List -->
    <div class="row">
        @if ($cards->isEmpty())
            <p class="text-center text-muted">You have no credit cards yet.</p>
        @endif
        @foreach ($cards as $card)
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-credit-card"></i> {{ $card->card_number }}</h5>
                        <p class="card-text">
                            Exp: {{ str_pad($card->expiry_month, 2, '0', STR_PAD_LEFT) }}/{{ $card->expiry_year }} <br>
                            CVV: *** 
                        </p>
                        <div class="d-flex justify-content-between">
                            <form action="{{ route('client.banks.destroy', $card->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</button>
                            </form>
                            {{-- <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editCardModal{{ $card->id }}">
                                <i class="fas fa-edit"></i> Edit
                            </button> --}}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add New Card -->
    <div class="card mt-4">
        <div class="card-header bg-primary text-white"><i class="fas fa-plus"></i> Add New Card</div>
        <div class="card-body">
            <form method="POST" action="{{ route('client.banks.store') }}">
                @csrf
                @include('auth.client.bank.form')
                <button class="btn btn-success mt-3"><i class="fas fa-check"></i> Add Card</button>
            </form>
        </div>
    </div>
</div>
@endsection
