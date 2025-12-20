@extends('layouts.app')

@section('title', 'Company Transactions')

@section('content')
<div class="container py-4">

    <h2 class="fw-bold mb-4">
        <i class="bi bi-table"></i> Company Transactions
    </h2>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">

            <table class="table table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Balance Before</th>
                        <th>Balance After</th>
                        <th>Trip </th>
                        <th>Booking Client</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $t)
                        <tr>
                            <td>{{ $t->id }}</td>
                            <td class="fw-bold text-primary">{{ number_format($t->amount) }} UZS</td>
                            <td>{{ number_format($t->balance_before) }}</td>
                            <td>{{ number_format($t->balance_after) }}</td>
                            <td>{{ $t->trip->startQuarter->name ?? 'N/A' }} → {{ $t->trip->endQuarter->name ?? 'N/A' }}</td>
                            <td>{{ $t->booking->user->first_name ?? '' }} {{ $t->booking->user->last_name ?? '' }}</td>
                            <td>
                                <span class="badge 
                                    @if($t->type=='income') bg-success 
                                    @else bg-danger @endif">
                                    {{ ucfirst($t->type) }}
                                </span>
                            </td>
                            <td style="max-width: 300px; white-space: wrap;">
                                {{ $t->reason }}
                            </td>
                            <td>{{ $t->created_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

    <div class="mt-3">
        {{ $transactions->links() }}
    </div>

</div>
@endsection
