@extends('layouts.app')

@section('title', 'Driver Payments')

@section('content')
<div class="container">
    <h1 class="my-4 text-center">Driver Payments</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Admin</th>
                <th>Driver</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ $payment->admin->name }}</td>
                    <td>{{ $payment->driver->name }}</td>
                    <td>{{ number_format($payment->amount, 0, '.', ' ') }} so'm</td>
                    <td>{{ $payment->transaction_date }}</td>
                    <td>
                        <form action="{{ route('driver-payments.destroy', $payment->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('To‘lov tarixini o‘chirmoqchimisiz?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-center mt-3">
        {{ $payments->links() }}
    </div>
</div>
@endsection
