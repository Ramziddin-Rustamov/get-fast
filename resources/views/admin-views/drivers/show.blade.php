@extends('layouts.app')

@section('title', 'Driver Details')

@section('content')
<div class="container py-4">

    <a href="{{ route('drivers.index') }}" class="btn btn-secondary mt-3">Back to List</a>
    <h1 class="mb-4 text-center">🚖 Driver Details</h1>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

  {{-- Basic Info --}}
<div class="card mb-4 shadow-lg border-0 rounded-4">
    <div class="card-header bg-primary text-white rounded-top-4">
        <h4 class="mb-0"><i class="fas fa-user"></i> {{ $driver->first_name }} {{ $driver->last_name }}</h4>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-6">
                <p class="mb-1"><strong>📞 Telefon:</strong> <span class="text-dark">{{ $driver->phone }}</span></p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>🛡 Rol:</strong> 
                    <span class="badge bg-primary text-white px-3 py-2 rounded-pill">{{ ucfirst($driver->role) }}</span>
                </p>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-6">
                <p class="mb-1"><strong>✅ Status:</strong> 
                    @if($driver->is_verified)
                        <span class="badge bg-success px-3 py-2 rounded-pill">Tasdiqlangan</span>
                    @else
                        <span class="badge bg-danger px-3 py-2 rounded-pill">Tasdiqlanmagan</span>
                    @endif
                </p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>🌍 Region:</strong> {{ $driver->region->name ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-6">
                <p class="mb-1"><strong>🏘 District:</strong> {{ $driver->district->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>📌 Quarter:</strong> {{ $driver->quarter->name ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>





{{-- Transfer Modal --}}
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">💸 Transfer Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('drivers.transfer', $driver->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" name="amount" id="amount" class="form-control" min="1000" 
                               max="{{ $driver->balance->sum('balance') }}" placeholder="Enter amount">
                    </div>

                    <div class="mb-3">
                        <label for="card_number" class="form-label">Kartasi</label>
                        <select name="card_id" id="card_id" class="form-control">
                            @foreach ($driver->cards->where('status', 'verified') as $card)
                                <option value="{{ $card->id }}">{{ $card->number }} - {{ $card->expiry_month }}/{{ $card->expiry }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea name="note" id="note" class="form-control" rows="2" placeholder="Optional note"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Withdraw Modal --}}
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">🏧 Withdraw Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
            <form action="{{ route('users.admin.withdraw', $driver->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="minus">

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number"
                               name="amount"
                               class="form-control"
                               min="1"
                               max="{{ $driver->balance->balance }}"
                               placeholder="Enter amount"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="note"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Withdraw sababi"></textarea>
                    </div>

                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-minus-circle"></i> Withdraw
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Pay Modal --}}
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">💰 Pay Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('users.admin.balance.add', $driver->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="plus">

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number"
                               name="amount"
                               class="form-control"
                               min="1"
                               placeholder="Enter amount"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Pay izohi"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus-circle"></i> Pay
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

   {{-- Balance --}}
   <div class="card mb-4 shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title">💰 Balance</h5>
            <p class="fs-4">So'm {{ number_format($driver->balance->balance, 2, '.', ' ') ?? '0' }}</p>
        </div>

                 {{-- Transfer to card --}}
                <button class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#transferModal">
                <i class="fas fa-exchange-alt"></i> Transfer to card
                </button>

                {{-- Withdraw (minus) --}}
                <button class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#withdrawModal">
                <i class="fas fa-minus-circle"></i> Withdraw
                </button>

                {{-- Pay (plus) --}}
                <button class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#payModal">
                <i class="fas fa-plus-circle"></i> Pay by Company Account
    </button>
    </div>
</div>


{{-- driver active card  --}}

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5 class="card-title">💳 Active Card</h5>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Kartasi</th>
                        <th>Expire</th>
                        <th>status</th>
                        <th>Ulangan nomer</th>

                        <th>Yaratilgan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($driver->cards->where('status', 'verified') as $card)
                    <tr class="text-center">
                        <td>{{ $card->id }}</td>
                        <td>{{ $card->number }}</td>
                        <td>{{ $card->expiry }}</td>
                        <td>{{ $card->status }}</td>
                        <td>{{ $card->phone }}</td>
                        <td>{{ $card->created_at->format('Y-m-d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>  



{{-- Balance Transactions --}}
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5 class="card-title">💳 Pul harakatlari ({{ $driver->balanceTransactions()->count() }})</h5>

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





    {{-- Trips --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">🗓 Trips ({{ $driver->driverTrips->count() }})</h5>

            @if ($driver->driverTrips->count())
                @foreach ($driver->driverTrips->sortByDesc('created_at') as $trip)
                    <div class="border rounded p-3 mb-3">

                        {{-- Trip header clickable --}}
                        <div 
                            class="d-flex justify-content-between align-items-center" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#trip_{{ $trip->id }}" 
                            style="cursor: pointer;"
                        >
                            <div>
                                <div><strong>From:</strong> {{ $trip->startQuarter->name ?? 'N/A' }}, {{ $trip->startQuarter->district->name_uz ?? '' }}</div>
                                <div><strong>To:</strong> {{ $trip->endQuarter->name ?? 'N/A' }}, {{ $trip->endQuarter->district->name ?? '' }}</div>
                                <div><strong>Time:</strong> 
                                    {{ \Carbon\Carbon::parse($trip->start_time)->format('d.m.Y H:i') }} - 
                                    {{ \Carbon\Carbon::parse($trip->end_time)->format('d.m.Y H:i') }}
                                </div>
                                <div><strong>Price:</strong> {{ number_format($trip->price_per_seat, 0, '.', ' ') }} so'm</div>
                                <div><strong>Seats:</strong> {{ $trip->available_seats }} available / {{ $trip->total_seats }} seats </div>
                            </div>

                            <span class="badge {{ $trip->status === 'cancelled' ? 'bg-danger' : 'bg-success' }}">
                                {{ ucfirst($trip->status) }}
                            </span>
                        </div>

                        {{-- BOOKING COLLAPSE --}}
                        <div id="trip_{{ $trip->id }}" class="collapse mt-3">

                            @if ($trip->bookings->count())
                                <div class="p-3 bg-light rounded">

                                    <h6 class="mb-2">📌 Bookings ({{ $trip->bookings->count() }})</h6>

                                    @foreach ($trip->bookings as $booking)
                                        <div class="border rounded p-2 mb-2 bg-white">
                                            <div><strong>User:</strong> {{ $booking->user->first_name ?? 'N/A' }} {{ $booking->user->last_name ?? '' }}</div>
                                            <div><strong>Phone:</strong> {{ $booking->user->phone ?? 'N/A' }}</div>
                                            <div><strong>Seats:</strong> {{ $booking->seats }}</div>
                                            <div><strong>Total Price:</strong> 
                                                {{ number_format($booking->total_price, 0, '.', ' ') }} so'm
                                            </div>
                                            <div><strong>Status:</strong>
                                                <span class="badge 
                                                    {{ $booking->status == 'cancelled' ? 'bg-danger' : 'bg-primary' }}">
                                                    {{ ucfirst($booking->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>

                            @else
                                <p class="text-muted">No bookings for this trip.</p>
                            @endif

                        </div>

                    </div>
                @endforeach
            @else
                <p class="text-muted">No trips available.</p>
            @endif
        </div>
    </div>




   {{-- Driver Documents --}}
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5 class="card-title d-flex justify-content-between">
            <span>📄 Haydovchi Hujjatlari</span>

            {{-- Hamma hujjatlarni o‘chirish --}}
            <form action="{{ route('driver.images.deleteAll', $driver->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm"
                        onclick="return confirm('Hamma hujjatlar o‘chirilsinmi?')">
                    Hamma Hujjatlarni O‘chirish
                </button>
            </form>
        </h5>

        <div class="row">

            @foreach($driverImages as $img)
                <div class="col-md-4 mb-3">
                    <div class="border rounded p-2 text-center shadow-sm">

                        <p class="fw-bold text-capitalize mb-1">
                            {{ str_replace('_', ' ', $img->type) }} 
                            @if($img->side)
                                ({{ ucfirst($img->side) }})
                            @endif
                        </p>

                        <img src="{{ asset('storage/' . $img->image_path) }}"
                             class="img-fluid rounded shadow-sm doc-preview"
                             style="cursor: zoom-in; max-height: 200px; object-fit: cover;"
                             data-bs-toggle="modal"
                             data-bs-target="#imageModal"
                             data-img="{{ asset('storage/' . $img->image_path) }}"
                        >

                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>


  {{-- Vehicles --}}
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5 class="card-title">🚘 Moshinalar</h5>

        @if($vehicles->count())
            @foreach($vehicles as $vehicle)
                
                <div class="border rounded p-3 mb-3">

                    {{-- VEHICLE HEADER (click to open images) --}}
                    <div class="d-flex justify-content-between align-items-center"
                         data-bs-toggle="collapse"
                         data-bs-target="#vehicle_{{ $vehicle->id }}"
                         style="cursor: pointer;">
                        
                        <div>
                            <p class="mb-1"><strong>Model:</strong> {{ $vehicle->model }}</p>
                            <p class="mb-1"><strong>Color:</strong> {{ $vehicle->color->title_uz }}</p>
                            <p class="mb-1"><strong>Seats:</strong> {{ $vehicle->seats }}</p>
                            <p class="mb-1"><strong>Raqami:</strong> {{ $vehicle->car_number }}</p>
                            <p class="mb-1"><strong>License Plate:</strong> {{ $vehicle->tech_passport_number }}</p>
                        </div>

                        <span class="badge bg-primary">Rasmlarni Ko‘rish</span>
                    </div>

                    @php
                    $images = $vehicleImages->where('vehicle_id', $vehicle->id);
                @endphp

                @if ($images->count())

                    {{-- Delete all images for this vehicle --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold">📸 Moshina Rasmlari</h6>

                        <form action="{{ route('vehicle.images.deleteAll', $vehicle->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('Hamma moshina rasmlari o‘chirilsinmi?')">
                                O‘chirish
                            </button>
                        </form>
                    </div>

                    <div class="row">
                        @foreach($images as $vimg)
                            <div class="col-md-3 mb-3">
                                <div class="border rounded p-2 text-center shadow-sm">

                                    <p class="fw-bold mb-1">
                                        {{ str_replace('_', ' ', $vimg->type) }}
                                        @if($vimg->side)
                                            ({{ ucfirst($vimg->side) }})
                                        @endif
                                    </p>

                                    <img src="{{ asset('storage/' . $vimg->image_path) }}"
                                         class="img-fluid rounded shadow-sm vehicle-preview"
                                         style="cursor: zoom-in; max-height: 160px; object-fit: cover;"
                                         data-bs-toggle="modal"
                                         data-bs-target="#imageModal"
                                         data-img="{{ asset('storage/' . $vimg->image_path) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>

                @else
                    <p class="text-muted">Rasmlar mavjud emas.</p>
                @endif



                </div>

            @endforeach

            {{-- Pagination --}}
            @if($vehicles->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $vehicles->links('pagination::bootstrap-5') }}
                </div>
            @endif

        @else
            <p class="text-muted">No vehicles assigned.</p>
        @endif
    </div>
</div>





   {{-- Driver Status --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
            <h5 class="card-title mb-2">🚦 Haydovchi statusi</h5>

            <form action="{{ route('drivers.updateStatus', $driver->id) }}" method="POST" class="d-flex align-items-center gap-2 mb-2">
                @csrf

                <select name="status" class="form-select form-select-sm">
                    <option value="none" {{ $driver->driving_verification_status == 'none' ? 'selected' : '' }}>None</option>
                    <option value="pending" {{ $driver->driving_verification_status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $driver->driving_verification_status == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $driver->driving_verification_status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="blocked" {{ $driver->driving_verification_status == 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>

                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fas fa-check"></i> Saqlash
                </button>
            </form>

            <div>
                <strong>Joriy status:</strong>
                @php
                    $statusColor = match($driver->driving_verification_status) {
                        'none' => 'bg-secondary',
                        'pending' => 'bg-warning text-dark',
                        'approved' => 'bg-success',
                        'rejected' => 'bg-danger',
                        'blocked' => 'bg-dark',
                        default => 'bg-secondary'
                    };
                @endphp
                <span class="badge {{ $statusColor }} px-3 py-2 rounded-pill">
                    {{ ucfirst($driver->driving_verification_status) }}
                </span>
            </div>
        </div>
    </div>


    {{--  --}}

        {{-- Message Driver --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">✉ Send SMS to Driver</h5>

            <form action="{{ route('drivers.sendSms', $driver->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea name="message" id="message" class="form-control" rows="3" placeholder="Type your message..."></textarea>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>

</div>


<script>
    document.addEventListener('click', function(e) {
        if (e.target.matches('.doc-preview') || e.target.matches('.vehicle-preview')) {
            document.getElementById('modalImage').src = e.target.dataset.img;
        }
    });
    </script>

{{-- FontAwesome --}}
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
@endsection
