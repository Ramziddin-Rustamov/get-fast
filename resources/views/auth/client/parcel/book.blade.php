@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <!-- Flash messages -->
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
        </div>

        <!-- Parcel details -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-box"></i> Delivery Details
                </div>
                <div class="card-body">
                    <p><strong>Sender Address:</strong><br>
                        {{ $parcel->trip->startQuarter->district->region->name }} >
                        {{ $parcel->trip->startQuarter->district->name }} >
                        {{ $parcel->trip->startQuarter->name }}
                    </p>
                    <p><strong>Receiver Address:</strong><br>
                        {{ $parcel->trip->endQuarter->district->region->name }} >
                        {{ $parcel->trip->endQuarter->district->name }} >
                        {{ $parcel->trip->endQuarter->name }}
                    </p>
                    <p><strong>Delivery Time:</strong>
                        {{ \Carbon\Carbon::parse($parcel->trip->end_time)->format('d M Y, H:i') }}
                    </p>
                    <p><strong>Price per kg:</strong>
                        {{ number_format($parcel->price_per_kg, 0, ',', ' ') }} UZS
                    </p>
                    <p><strong>Max Weight:</strong>
                        {{ number_format($parcel->max_weight, 0, ',', ' ') }} kg
                    </p>
                </div>
            </div>
        </div>

        <!-- Parcel sending form -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-envelope"></i> Send Your Parcel
                </div>
                <div class="card-body">
                    <form action="{{ route('client.parcel.send') }}" method="POST">
                        @csrf
                        <input type="hidden" name="parcel_id" value="{{ $parcel->id }}">
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">

                        <div class="mb-3">
                            <label class="form-label">Sender Phone</label>
                            <input type="text" value="{{ Auth::user()->phone }}" class="form-control" name="sender_phone" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Receiver Phone</label>
                            <input type="text" class="form-control" name="receiver_phone" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Parcel Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="e.g. Documents, Small Electronics..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" class="form-control" name="weight" step="0.1" max="{{ $parcel->max_weight }}" maxlength="3" required>
                        </div>

                        {{-- <!-- Card payment -->
                        <div class="mb-3">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" name="card_number" placeholder="0000-0000-0000-0000" pattern="\d{4}-\d{4}-\d{4}-\d{4}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry Month</label>
                                <input type="number" class="form-control" name="expiry_month" min="1" max="12" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry Year</label>
                                <input type="number" class="form-control" name="expiry_year" min="{{ now()->year }}" max="2040" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">CVV</label>
                            <input type="text" class="form-control" name="cvv" minlength="3" maxlength="3" required>
                        </div> --}}

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-paper-plane"></i> Submit Parcel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
