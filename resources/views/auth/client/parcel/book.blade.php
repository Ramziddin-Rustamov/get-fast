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
                            <input type="text" disabled value="{{ Auth::user()->phone }}" class="form-control" name="sender_phone" required>
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
                            <input type="number" class="form-control" name="max_weight" step="0.1" max="{{ $parcel->max_weight }}" maxlength="3" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Payment Method')}}</label>
                            <input type="text" class="form-control" name="payment_method" value="Credit Card" required disabled>
                        </div>

                        <div class="mb-3">
                            @if(!Auth::user()->clientCard)
                            <a href="{{ route('client.banks.index') }}" class="btn btn-primary">Add Card</a>
                            @else
                            <label class="form-label">{{ __('Your Credit Card ') }}</label>
                                <input type="text" class="form-control" name="card_number" value="{{ Auth::user()->clientCard->card_number }}" required disabled>
                            @endif
                        </div>
                        

                        @if(!Auth::user()->clientCard)  
                        <button type="submit" class="btn btn-success w-100" disabled>
                            <i class="fas fa-paper-plane"></i> Submit Parcel
                        </button>
                        @else
                        <button type="submit" class="btn btn-success w-100">
                            
                            <i class="fas fa-paper-plane"></i> Submit Parcel
                        </button>
                        @endif
                        
                      
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
