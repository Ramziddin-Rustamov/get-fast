@extends('layouts.app')

@section('title', 'PUl surash')
@section('content')
<div class="container-fluid mt-4 pt-4">

    <div class="card shadow rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">💸 Withdraw Requests</h4>
        </div>

        <div class="card-body">

            {{-- Alerts --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Amount</th>
                            <th>Card</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($withdraws as $w)
                            <tr>
                                <td>{{ $w->id }}</td>

                                <td>
                                    <strong>{{ $w->user->first_name ?? '—' }}</strong><br>
                                    <small class="text-muted">{{ $w->user->phone ?? '' }}</small>
                                </td>

                                <td>
                                    <span class="badge bg-secondary text-uppercase">
                                        {{ $w->role }}
                                    </span>
                                </td>

                                <td>
                                    <strong>{{ number_format($w->amount, 0) }} UZS</strong>
                                </td>

                                <td>
                                    <code>{{ $w->card_number }}</code><br>
                                    <small>{{ $w->card_holder }}</small>
                                </td>

                                <td>
                                    @if($w->status == 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($w->status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($w->status == 'pending')

                                        {{-- APPROVE --}}
                                        <form action="{{ route('admin.withdraw.approve', $w->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-success btn-sm"
                                                onclick="return confirm('Tasdiqlaysizmi?')">
                                                ✅
                                            </button>
                                        </form>

                                        {{-- REJECT --}}
                                        <form action="{{ route('admin.withdraw.reject', $w->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('Rad etilsinmi?')">
                                                ❌
                                            </button>
                                        </form>

                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-end mt-3">
                {{ $withdraws->links() }}
            </div>

        </div>
    </div>

</div>
@endsection