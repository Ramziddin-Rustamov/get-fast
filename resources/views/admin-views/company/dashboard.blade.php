@extends('layouts.app')

@section('title', 'Company Dashboard')

@section('content')
<div class="container py-4">

    <h2 class="fw-bold mb-4">
        <i class="bi bi-speedometer2"></i> Company Dashboard
    </h2>

    {{-- COMPANY BALANCE --}}
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-secondary">Current Balance</h6>
                    <h2 class="fw-bold text-success">{{ number_format($company->balance) }} UZS</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-secondary">Total Income</h6>
                    <h2 class="fw-bold text-primary">{{ number_format($company->total_income) }} UZS</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-secondary">Today Income</h6>
                    <h2 class="fw-bold text-warning">{{ number_format($todayIncome) }} UZS</h2>
                </div>
            </div>
        </div>
    </div>



    {{-- BOOKINGS --}}
    <h4 class="mt-5 fw-bold">Bookings Statistics</h4>
    <div class="row g-4">

        @php
            $bookingStats = [
                ['title' => 'Total Bookings', 'count' => $totalBookings, 'color' => 'primary'],
                ['title' => 'Confirmed', 'count' => $confirmedBookings, 'color' => 'success'],
                ['title' => 'Cancelled', 'count' => $cancelledBookings, 'color' => 'danger'],
                ['title' => 'Completed', 'count' => $completedBookings, 'color' => 'info'],
            ];
        @endphp

        @foreach ($bookingStats as $b)
        <div class="col-md-2">
            <div class="card shadow rounded-4 border-0">
                <div class="card-body text-center">
                    <h6 class="text-{{ $b['color'] }}">{{ $b['title'] }}</h6>
                    <h2 class="fw-bold">{{ $b['count'] }}</h2>
                </div>
            </div>
        </div>
        @endforeach

    </div>



    {{-- USERS --}}
    <h4 class="mt-5 fw-bold">Users Overview</h4>

    <div class="row g-4">

        <div class="col-md-3">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-info">Total Clients</h6>
                    <h2 class="fw-bold">{{ $totalClients }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-warning">Total Drivers</h6>
                    <h2 class="fw-bold">{{ $totalDrivers }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-success">Active Users</h6>
                    <h2 class="fw-bold">{{ $activeUsers }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-danger">Inactive Users</h6>
                    <h2 class="fw-bold">{{ $inactiveUsers }}</h2>
                </div>
            </div>
        </div>

    </div>



    {{-- DRIVER VERIFICATION --}}
    <h4 class="mt-5 fw-bold">Driver Verification Status</h4>

    <div class="row g-4">

        @php
            $driverStats = [
                ['title' => 'Approved', 'count' => $driversApproved, 'color' => 'success'],
                ['title' => 'Rejected', 'count' => $driversRejected, 'color' => 'danger'],
                ['title' => 'Pending', 'count' => $driversPending, 'color' => 'warning'],
                ['title' => 'Blocked', 'count' => $driversBlocked, 'color' => 'dark'],
            ];
        @endphp

        @foreach ($driverStats as $d)
        <div class="col-md-3">
            <div class="card shadow rounded-4 border-0">
                <div class="card-body text-center">
                    <h6 class="text-{{ $d['color'] }}">{{ $d['title'] }}</h6>
                    <h2 class="fw-bold">{{ $d['count'] }}</h2>
                </div>
            </div>
        </div>
        @endforeach

    </div>



    {{-- CARDS --}}
    <h4 class="mt-5 fw-bold">Cards Overview</h4>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-primary">Total Cards</h6>
                    <h2 class="fw-bold">{{ $totalCards }}</h2>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
