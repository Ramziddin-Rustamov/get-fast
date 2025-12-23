@extends('layouts.app')

@section('title', 'Client Details')

@section('content')
<div class="container py-4">

    <a href="{{ route('clients.index') }}" class="btn btn-secondary mt-3">⬅ Back to List</a>
    <h1 class="mb-4 text-center">👤 Client Details</h1>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif


    {{-- Basic Info --}}
    <div class="card mb-4 shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
            <h4 class="mb-0">
                <i class="fas fa-user"></i> 
                {{ $client->first_name }} {{ $client->last_name }}
            </h4>
        </div>

        <div class="card-body">

            <div class="row mb-2">
                <div class="col-md-6">
                    <p><strong>📞 Telefon:</strong> {{ $client->phone }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>🛡 Rol:</strong> 
                        <span class="badge bg-info px-3 py-2 rounded-pill">
                            {{ ucfirst($client->role) }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                    <p>
                        <strong>📍 Region:</strong> 
                        {{ $client->region->name_uz ?? 'N/A' }}
                    </p>
                </div>
                <div class="col-md-6">
                    <p>
                        <strong>🏘 District:</strong> 
                        {{ $client->region->district->name_uz ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                    <p>
                        <strong>📌 Quarter:</strong> 
                        {{ $client->region->district->quarter->name ?? 'N/A' }}
                    </p>
                </div>

                <div class="col-md-6">
                    <p>
                        <strong>✔ SMS Tasdiqlanganmi:</strong>
                        @if($client->is_verified)
                            <span class="badge bg-success px-3 py-2 rounded-pill">Ha</span>
                        @else
                            <span class="badge bg-danger px-3 py-2 rounded-pill">Yo'q</span>
                        @endif
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- Client Trips --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h4 class="card-title mb-3">
                <i class="fas fa-calendar-check text-primary"></i>
                <strong>Buyurtmalar</strong> 
                <span class="badge bg-primary ms-2">{{ $client->bookings->count() }}</span>
            </h4>
    
            @if($client->bookings->count())
                @foreach($client->bookings->sortByDesc('created_at') as $booking)
                    <div class="border rounded p-3 mb-3 bg-white shadow-sm">
    
                        {{-- HEADER --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-dark px-3 py-2">#{{ $booking->id }}</span>
    
                            <span class="badge 
                                {{ $booking->status === 'expired' ? 'bg-danger' : ($booking->status === 'cancelled' ? 'bg-secondary' : 'bg-success') }}  
                                px-3 py-2">
                                <i class="fas fa-info-circle"></i> 
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>
    
                        <hr>
    
                        {{-- MAIN GRID --}}
                        <div class="row mb-2">
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-users text-primary"></i>
                                <strong>Seats:</strong>
                                {{ $booking->seats_booked }}
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-money-bill-wave text-success"></i>
                                <strong>Total Price:</strong>
                                {{ number_format($booking->total_price, 0, '.', ' ') }} so'm
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-clock text-info"></i>
                                <strong>Created:</strong>
                                {{ $booking->created_at->format('d.m.Y H:i') }}
                            </div>
    
                        </div>
    
                        {{-- PASSENGERS --}}
                        <div class="mt-3">
                            <h6 class="fw-bold">
                                <i class="fas fa-user-friends text-primary"></i>
                                Yo‘lovchilar ({{ $booking->passengers->count() }})
                            </h6>
    
                            @if($booking->passengers->count())
                                <ul class="list-group mt-2">
                                    @foreach($booking->passengers as $p)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-user-circle me-2 text-secondary"></i>
                                                <strong>{{ $p->name }}</strong>
                                            </div>
                                            <div>
                                                <i class="fas fa-phone-alt text-success me-1"></i>
                                                {{ $p->phone }}
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mt-1">Yo‘lovchi qo‘shilmagan.</p>
                            @endif
                        </div>
    
                        <hr>
    
                        {{-- TRIP INFO --}}
                        <div class="row mb-2">
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-map-marker-alt text-danger"></i>
                                <strong>From:</strong><br>
                                {{ $booking->trip->startQuarter->name ?? 'N/A' }} —
                                {{ $booking->trip->startQuarter->district->name_uz ?? '' }},
                                {{ $booking->trip->startQuarter->district->region->name_uz ?? '' }}
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-flag-checkered text-success"></i>
                                <strong>To:</strong><br>
                                {{ $booking->trip->endQuarter->name ?? 'N/A' }} —
                                {{ $booking->trip->endQuarter->district->name_uz ?? '' }},
                                {{ $booking->trip->endQuarter->district->region->name_uz ?? '' }}
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-hourglass-start text-primary"></i>
                                <strong>Start Time:</strong>
                                {{ \Carbon\Carbon::parse($booking->trip->start_time)->format('d.m.Y H:i') }}
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-hourglass-end text-danger"></i>
                                <strong>End Time:</strong>
                                {{ \Carbon\Carbon::parse($booking->trip->end_time)->format('d.m.Y H:i') }}
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-money-check-alt text-success"></i>
                                <strong>Seat Price:</strong>
                                {{ number_format($booking->trip->price_per_seat, 0, '.', ' ') }} so'm
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-calendar-plus text-secondary"></i>
                                <strong>Trip Created:</strong>
                                {{ \Carbon\Carbon::parse($booking->trip->created_at)->format('d.m.Y H:i') }}
                            </div>
    
                        </div>
    
                    </div>
                @endforeach

              {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $bookings->links('pagination::bootstrap-5') }}
            </div>
            @else
                <p class="text-muted">Hozircha buyurtmalar mavjud emas.</p>
            @endif
        </div>
    </div>
    



   {{-- Balance --}}
   <div class="card mb-4 shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title">💰 Balance</h5>
            <p class="fs-4">
                So'm {{ number_format(optional($client->balance)->balance ?? 0, 2, '.', ' ') }}
            </p>
        </div>
        

        {{-- Transfer / Pay button --}}
        {{-- <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#transferModal">
            <i class="fas fa-money-bill-wave"></i> Transfer
        </button> --}}
    </div>
</div>

{{-- Balance Transactions --}}
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5 class="card-title">💳 Pul harakatlari ({{ $client->balanceTransactions()->count() }})</h5>

        @if($balanceTransactions->count())
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>#</th>
                            <th>Tur</th>
                            <th>Summa</th>
                            <th>Balans oldin</th>
                            <th>Balans keyin</th>
                            <th>Trip ID</th>
                            <th>Holat</th>
                            <th>Sabab / Izoh</th>
                            <th>Sana</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balanceTransactions as $transaction)
                        <tr class="text-center">
                            <td>{{ $transaction->id }}</td>
                            <td>
                                <span class="badge {{ $transaction->type === 'debit' ? 'bg-danger' : 'bg-success' }}">
                                    {{ $transaction->type === 'debit' ? 'Chiqim' : 'Kirim' }}
                                </span>
                            </td>
                            <td>So'm {{ number_format($transaction->amount, 2, '.', ' ') }}</td>
                            <td>So'm {{ number_format($transaction->balance_before, 2, '.', ' ') }}</td>
                            <td>So'm {{ number_format($transaction->balance_after, 2, '.', ' ') }}</td>
                            <td>{{ $transaction->trip_id ?? '-' }}</td>
                            <td>
                                @if($transaction->status === 'success')
                                    <span class="badge bg-success">Muvaffaqiyatli</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="badge bg-warning text-dark">Kutilmoqda</span>
                                @else
                                    <span class="badge bg-danger">Xato</span>
                                @endif
                            </td>
                            <td>{{ $transaction->reason ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d.m.Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($balanceTransactions->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $balanceTransactions->links('pagination::bootstrap-5') }}
            </div>
            @endif

        @else
            <p class="text-muted text-center">Hozircha pul harakatlari mavjud emas.</p>
        @endif
    </div>
</div>



    {{-- Send SMS --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">✉ Clientga SMS yuborish</h5>

            <form action="{{ route('clients.sendSms', $client->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="message" class="form-label">Xabar</label>
                    <textarea name="message" id="message" class="form-control" rows="3"
                              placeholder="Xabar yozing..."></textarea>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Yuborish
                </button>
            </form>
        </div>
    </div>

</div>

{{-- FontAwesome --}}
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

@endsection
