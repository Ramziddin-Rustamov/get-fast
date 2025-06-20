@extends('layouts.app')

@section('title', 'Add New Order')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{-- Page Heading --}}
            <h2 class="text-center mb-4">📝 Add New Order</h2>

            {{-- Order Form --}}
            <div class="card shadow rounded">
                <div class="card-body">
                    <form action="{{ route('orders.store') }}" method="POST">
                        @csrf

                        {{-- Trip ID --}}
                  <!-- Trip ID Dropdown -->
                <div class="mb-3">
                    <label for="trip_id" class="form-label">🚗 Trip</label>
                    <select class="form-control" id="trip_id" name="trip_id" required>
                        <option value="">-- Select a Trip --</option>
                        @foreach($trips as $trip)
                            <option value="{{ $trip->id }}">
                                From: {{ $trip->startQuarter->name ?? 'N/A' }} → To: {{ $trip->endQuarter->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- User ID Dropdown -->
                <div class="mb-3">
                    <label for="user_id" class="form-label">👤 User</label>
                    <select class="form-control" id="user_id" name="user_id" required>
                        <option value="">-- Select a User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone }})</option>
                        @endforeach
                    </select>
                </div>


                        {{-- Seats Booked --}}
                        <div class="mb-3">
                            <label for="seats_booked" class="form-label">💺 Seats Booked</label>
                            <input type="number" class="form-control" id="seats_booked" name="seats_booked" required>
                        </div>

                        {{-- Total Price --}}
                        <div class="mb-3">
                            <label for="total_price" class="form-label">💰 Total Price (so'm)</label>
                            <input type="number" step="0.01" class="form-control" id="total_price" name="total_price" required>
                        </div>

                        {{-- Status --}}
                        <div class="mb-3">
                            <label for="status" class="form-label">📦 Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="canceled">Canceled</option>
                            </select>
                        </div>

                        {{-- Submit Button --}}
                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- FontAwesome --}}
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
@endsection
