@extends('layouts.app')

@section('title', 'Drivers List')

@section('content')
<div class="container">
    <h1 class="my-4 text-center">Drivers</h1>

    <!-- Search Bar -->
    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('drivers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Driver
            </a>
        </div>
        <div class="col-md-6">
            <form action="{{ route('drivers.index') }}" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search driver..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Drivers Table -->
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Balance</th>
                    <th>Trips</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($drivers as $driver)
                    <tr>
                        <td>{{ $driver->id }}</td>
                        <td>{{ $driver->name }}</td>
                        <td>{{ $driver->phone }}</td>
                        <td>So'm {{ number_format($driver->balance->sum('balance'),2) ?? 'No Data' }}</td>
                        <td>{{ $driver->driverTrips->count() }}</td>
                        <td>
                            <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"> view</i>
                            </a>
                            <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"> Edit</i>
                            </a>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $driver->id }}">
                                <i class="fas fa-trash"> delate</i>
                            </button>

                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#pay{{ $driver->id }}">
                                <i class="fas fa-pen"> Pay</i>
                            </button>

                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" class="bg-light">
                            <strong>Location:</strong> <br>
                            {{ $driver->region->name ?? 'No Region' }},  <br>
                            {{ $driver->district->name ?? 'No District' }},  <br>
                            {{ $driver->quarter->name ?? 'No Quarter' }} <br>
                            <br>
                            <hr>
                            <strong class="text-dark">Vehicles: <br></strong> 
                            @if ($driver->vehicles->isNotEmpty())
                                @foreach($driver->vehicles as $vehicle)
                                    <span class="badge bg-primary">{{ $vehicle->model }} ({{ $vehicle->year }}) </span>
                                    <span class="badge bg-primary"> Seats {{$vehicle->seats}}</span> <br>
                                @endforeach
                            @else
                                <span class="text-muted">No Vehicles</span>
                            @endif
                            <br>

                        </td>
                        
                    </tr>
                    <tr>
                        <td>
                            <strong>Trips   :</strong> {{ $driver->driverTrips->count() }}
                            @if ($driver->driverTrips->isNotEmpty())
                                <ul class="list-group mt-2">
                                    @foreach($driver->driverTrips as $trip)
                                        <li class="list-group-item">
                                            <strong>From:</strong> {{ $trip->start_location }} → 
                                            <strong>To:</strong> {{ $trip->end_location }} <br>
                                            <strong>Start:</strong> {{ \Carbon\Carbon::parse($trip->start_time)->format('d.m.Y H:i') }} <br>
                                            <strong>End:</strong> {{ \Carbon\Carbon::parse($trip->end_time)->format('d.m.Y H:i') }} <br>
                                            <strong>Price:</strong> {{ number_format($trip->price_per_seat, 0, '.', ' ') }} so'm <br>
                                            <strong>Seats:</strong> Band emas :{{ $trip->available_seats }} / Total {{ $trip->total_seats }} <br>
                                            <strong>Status:</strong> 
                                            <span class="badge {{ $trip->status === 'expired' ? 'bg-danger' : 'bg-success' }}">
                                                {{ ucfirst($trip->status) }}
                                            </span>
                                        </li> <br>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">No Trips</p>
                            @endif
                        </td>
                        
                    </tr>

                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="deleteModal{{ $driver->id }}" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirm Deletion</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete <strong>{{ $driver->name }}</strong>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form action="{{ route('drivers.destroy', $driver->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="pay{{ $driver->id }}" tabindex="-1" aria-labelledby="pay" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirm Pay</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to pay  to <strong>{{ $driver->name }}</strong>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form action="{{ route('drivers.reset-balance', $driver->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Yes, Pay</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-3">
        {{ $drivers->links() }}
    </div>
</div>

<!-- FontAwesome Icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

@endsection
