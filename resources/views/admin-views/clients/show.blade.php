@extends('layouts.app')

@section('title', 'Client Details')

@push('styles')
<style>
    .k-page { max-width: 1100px; }
    .k-hero {
        background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
        color: #fff; border-radius: 20px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 24px 50px -24px rgba(14,165,233,.6);
    }
    .k-hero .k-avatar { width: 60px; height: 60px; border-radius: 16px; background: rgba(255,255,255,.2); display: grid; place-items: center; font-size: 1.5rem; }
    .k-hero h1 { font-size: 1.6rem; margin: 0; color: #fff; }

    .k-card { background: #fff; border: 1px solid #eef2f7; border-radius: 18px; box-shadow: 0 18px 40px -28px rgba(11,19,36,.45); }
    .k-card .k-card-head { display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; }
    .k-card .k-card-body { padding: 1.25rem; }
    .k-title { display: flex; align-items: center; gap: .6rem; font-size: 1.05rem; font-weight: 700; margin: 0; }
    .k-chip { width: 38px; height: 38px; border-radius: 11px; display: grid; place-items: center; color: #fff; font-size: .95rem; background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2)); }

    .k-info { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: .85rem 1.5rem; }
    @media (max-width: 575px){ .k-info { grid-template-columns: 1fr; } }
    .k-info .lbl { font-size: .78rem; text-transform: uppercase; letter-spacing: .03em; color: #94a3b8; font-weight: 700; margin: 0 0 .15rem; }
    .k-info .val { font-weight: 600; color: var(--k-ink); margin: 0; }

    .k-balance-amount { font-family: 'Sora', sans-serif; font-weight: 800; font-size: 1.9rem; line-height: 1; }
    .k-card thead th { background: var(--k-ink); color: #fff; font-weight: 600; font-size: .85rem; border: 0; }
</style>
@endpush

@section('content')
<div class="container k-page py-4">

    @if(session('success'))
        <div class="alert alert-success rounded-4 border-0 shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-4 border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    {{-- Hero --}}
    <div class="k-hero d-flex align-items-center gap-3 mb-4">
        <div class="k-avatar"><i class="fas fa-user"></i></div>
        <div class="me-auto">
            <h1>{{ $client->first_name }} {{ $client->last_name }}</h1>
            <div class="mt-1 opacity-75"><i class="fas fa-phone me-1"></i> {{ $client->phone }}</div>
        </div>
        <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-pen me-1"></i> Tahrirlash
        </a>
        <a href="{{ route('clients.index') }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-arrow-left me-1"></i> Ro‘yxat
        </a>
    </div>

    {{-- Basic Info --}}
    <div class="k-card mb-4">
        <div class="k-card-head">
            <h2 class="k-title"><span class="k-chip"><i class="fas fa-id-card"></i></span> Asosiy ma'lumotlar</h2>
        </div>
        <div class="k-card-body">
            <div class="k-info">
                <div><p class="lbl">Telefon</p><p class="val">{{ $client->phone }}</p></div>
                <div><p class="lbl">Rol</p><p class="val"><span class="badge bg-info rounded-pill px-3 py-2">{{ ucfirst($client->role) }}</span></p></div>
                <div><p class="lbl">Region</p><p class="val">{{ $client->region->name_uz ?? 'N/A' }}</p></div>
                <div><p class="lbl">District</p><p class="val">{{ $client->district->name_uz ?? 'N/A' }}</p></div>
                <div><p class="lbl">Quarter</p><p class="val">{{ $client->quarter->name ?? 'N/A' }}</p></div>
                <div>
                    <p class="lbl">SMS tasdiqlanish</p>
                    <p class="val">
                        @if($client->is_verified)
                            <span class="badge bg-success rounded-pill px-3 py-2">Ha</span>
                        @else
                            <span class="badge bg-danger rounded-pill px-3 py-2">Yo'q</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Balance --}}
    <div class="k-card mb-4">
        <div class="k-card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <p class="lbl text-uppercase text-muted fw-bold mb-1" style="font-size:.78rem; letter-spacing:.03em;">Balance</p>
                <div class="k-balance-amount">{{ number_format($client->balance?->balance ?? 0, 2, '.', ' ') }} <span class="fs-6 text-muted">so'm</span></div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-success rounded-3" data-bs-toggle="modal" data-bs-target="#transferModal">
                    <i class="fas fa-exchange-alt me-1"></i> Transfer to card
                </button>
                <button class="btn btn-danger rounded-3" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                    <i class="fas fa-minus-circle me-1"></i> Withdraw
                </button>
                <button class="btn btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#payModal">
                    <i class="fas fa-plus-circle me-1"></i> Pay by Company Account
                </button>
            </div>
        </div>
    </div>

    {{-- Transfer Modal --}}
    <div class="modal fade" id="transferModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i>Transfer Balance</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('clients.transfer', $client->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" name="amount" id="amount" class="form-control" min="1000"
                                   max="{{ $client->balance?->balance }}" placeholder="Enter amount">
                        </div>
                        <div class="mb-3">
                            <label for="card_id" class="form-label">Kartasi</label>
                            <select name="card_id" id="card_id" class="form-control">
                                @foreach ($client->cards->where('status', 'verified') as $card)
                                    <option value="{{ $card->id }}">{{ $card->number }} - {{ $card->expiry_month }}/{{ $card->expiry }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label">Note</label>
                            <textarea name="note" id="note" class="form-control" rows="2" placeholder="Optional note"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-paper-plane me-1"></i> Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Withdraw Modal --}}
    <div class="modal fade" id="withdrawModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header bg-danger text-white rounded-top-4">
                    <h5 class="modal-title"><i class="fas fa-minus-circle me-2"></i>Withdraw Balance</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('users.admin.withdraw', $client->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="minus">
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" class="form-control" placeholder="Enter amount" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Withdraw sababi"></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100"><i class="fas fa-minus-circle me-1"></i> Withdraw</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Pay Modal --}}
    <div class="modal fade" id="payModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Pay Balance</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('users.admin.balance.add', $client->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="plus">
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" class="form-control" min="1" placeholder="Enter amount" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Note</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Pay izohi"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus-circle me-1"></i> Pay</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Card --}}
    <div class="k-card mb-4">
        <div class="k-card-head">
            <h2 class="k-title"><span class="k-chip"><i class="fas fa-credit-card"></i></span> Active Card</h2>
        </div>
        <div class="k-card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="text-center">
                        <tr>
                            <th>#</th><th>Kartasi</th><th>Expire</th><th>Status</th><th>Ulangan nomer</th><th>Yaratilgan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($client->cards->where('status', 'verified') as $card)
                        <tr class="text-center">
                            <td>{{ $card->id }}</td>
                            <td>{{ $card->number }}</td>
                            <td>{{ $card->expiry }}</td>
                            <td>{{ $card->status }}</td>
                            <td>{{ $card->phone }}</td>
                            <td>{{ $card->created_at->format('Y-m-d') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">Karta yo‘q.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Bookings --}}
    <div class="k-card mb-4">
        <div class="k-card-head">
            <h2 class="k-title"><span class="k-chip"><i class="fas fa-calendar-check"></i></span> Buyurtmalar ({{ $client->bookings->count() }})</h2>
            <a href="{{ route('clients.trips', $client->id) }}" class="btn btn-primary rounded-3">
                <i class="fas fa-calendar-check me-1"></i> Buyurtmalarni ko‘rish
            </a>
        </div>
        <div class="k-card-body">
            <p class="text-muted mb-0">Mijozning barcha buyurtmalari, yo‘lovchilari va trip ma'lumotlari alohida sahifada.</p>
        </div>
    </div>

    {{-- Balance Transactions --}}
    <div class="k-card mb-4">
        <div class="k-card-head">
            <h2 class="k-title"><span class="k-chip"><i class="fas fa-money-bill-transfer"></i></span> Pul harakatlari ({{ $client->balanceTransactions()->count() }})</h2>
            <a href="{{ route('clients.balance', $client->id) }}" class="btn btn-primary rounded-3">
                <i class="fas fa-money-bill-transfer me-1"></i> Pul harakatlarini ko‘rish
            </a>
        </div>
        <div class="k-card-body">
            <p class="text-muted mb-0">Mijozning barcha kirim/chiqim pul harakatlari alohida sahifada.</p>
        </div>
    </div>

    {{-- Make driver --}}
    <div class="k-card mb-4">
        <div class="k-card-head">
            <h2 class="k-title"><span class="k-chip"><i class="fas fa-user-shield"></i></span> Driver qilish <span class="text-muted fw-normal fs-6">(approved bo‘lsa client driverga o‘zgaradi)</span></h2>
            <div>
                <strong>Joriy status:</strong>
                @php
                    $statusColor = match($client->driving_verification_status) {
                        'none' => 'bg-secondary',
                        'pending' => 'bg-warning text-dark',
                        'approved' => 'bg-success',
                        'rejected' => 'bg-danger',
                        'blocked' => 'bg-dark',
                        default => 'bg-secondary'
                    };
                @endphp
                <span class="badge {{ $statusColor }} px-3 py-2 rounded-pill">{{ ucfirst($client->driving_verification_status) }}</span>
            </div>
        </div>
        <div class="k-card-body">
            <form action="{{ route('client.updateStatus', $client->id) }}" method="POST" class="d-flex align-items-center gap-2 flex-wrap">
                @csrf
                <select name="status" class="form-select form-select-sm" style="max-width: 220px;">
                    <option value="none" {{ $client->driving_verification_status == 'none' ? 'selected' : '' }}>None</option>
                    <option value="pending" {{ $client->driving_verification_status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $client->driving_verification_status == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $client->driving_verification_status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="blocked" {{ $client->driving_verification_status == 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
                <button type="submit" class="btn btn-sm btn-success rounded-3"><i class="fas fa-check me-1"></i> Saqlash</button>
            </form>
        </div>
    </div>

    {{-- Send SMS --}}
    <div class="k-card mb-4">
        <div class="k-card-head">
            <h2 class="k-title"><span class="k-chip"><i class="fas fa-paper-plane"></i></span> SMS yuborish</h2>
            <span class="badge bg-light text-dark border">Mijoz tili: {{ $client->authLanguage?->language ?? '—' }}</span>
        </div>
        <div class="k-card-body">
            <form action="{{ route('clients.sendSms', $client->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="message" class="form-label">Xabar</label>
                    <textarea name="message" id="message" class="form-control" rows="3" placeholder="Xabar yozing..."></textarea>
                </div>
                <button type="submit" class="btn btn-success rounded-3"><i class="fas fa-paper-plane me-1"></i> Yuborish</button>
            </form>
        </div>
    </div>

    {{-- Actions: verify + delete --}}
    <div class="d-flex justify-content-between flex-wrap gap-2">
        <a href="{{ route('client.markAsVerified', $client->id) }}"
           class="btn btn-success rounded-3"
           onclick="return confirm('Tasdiqlamoqchimisiz?')">
            <i class="fas fa-check-circle me-1"></i> Verify qilish
        </a>

        <form action="{{ route('client.deleteClient', $client->id) }}" method="POST"
              onsubmit="return confirm('Foydalanuvchini o‘chirmoqchimisiz?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger rounded-3"><i class="fas fa-trash me-1"></i> Delete User</button>
        </form>
    </div>

</div>
@endsection
