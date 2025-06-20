@extends('layouts.app')

@section('title', 'Drivers List')

@section('content')
<div class="container py-4">
    <h1 class="text-center mb-4">🚖 Drivers Management</h1>

    {{-- Search & Add New Driver --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6 mb-2 mb-md-0">
            <a href="{{ route('drivers.create') }}" class="btn btn-success shadow-sm">
                <i class="fas fa-plus-circle"></i> Add New Driver
            </a>
        </div>
        <div class="col-md-6">
            <form action="{{ route('drivers.index') }}" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="🔍 Search driver..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Drivers Table --}}
    <div class="table-responsive shadow rounded">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Balance</th>
                    <th>Trips</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                <tr>
                    <td class="text-center">{{ $driver->id }}</td>
                    <td>{{ $driver->name }}</td>
                    <td>{{ $driver->phone }}</td>
                    <td>So'm {{ number_format($driver->balance->sum('balance'), 2) ?? 'No Data' }}</td>
                    <td class="text-center">{{ $driver->driverTrips->count() }}</td>
                    <td class="text-center">
                        <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $driver->id }}"><i class="fas fa-trash-alt"></i></button>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#pay{{ $driver->id }}"><i class="fas fa-dollar-sign"></i></button>
                    </td>
                </tr>

                {{-- Extra Info Row --}}
                <tr>
                    <td colspan="6" class="bg-light">
                        {{-- Location --}}
                        <strong>📍 Location:</strong>
                        {{ $driver->region->name ?? 'No Region' }},
                        {{ $driver->district->name ?? 'No District' }},
                        {{ $driver->quarter->name ?? 'No Quarter' }}

                        <hr>

                        {{-- Vehicles --}}
                        <strong>🚘 Vehicles:</strong><br>
                        @forelse($driver->vehicles as $vehicle)
                            <span class="badge bg-info text-dark me-2 mb-1">
                                {{ $vehicle->model }} ({{ $vehicle->year }}) | Seats: {{ $vehicle->seats }}
                            </span>
                        @empty
                            <span class="text-muted">No Vehicles</span>
                        @endforelse

                        <hr>

                        {{-- Trips --}}
                        <strong>🗓 Trips ({{ $driver->driverTrips->count() }}):</strong>
                        @forelse($driver->driverTrips as $trip)
                            <div class="border rounded p-2 my-2">
                                <div><strong>From:</strong>
                                    {{ $trip->startQuarter->district->region->name }},
                                    {{ $trip->startQuarter->district->name }},
                                    <strong>{{ $trip->startQuarter->name }}</strong>
                                </div>

                                <div><strong>To:</strong>
                                    {{ $trip->endQuarter->district->region->name }},
                                    {{ $trip->endQuarter->district->name }},
                                    <strong>{{ $trip->endQuarter->name }}</strong>
                                </div>

                                <div><strong>Time:</strong>
                                    {{ \Carbon\Carbon::parse($trip->start_time)->format('d.m.Y H:i') }}
                                    -
                                    {{ \Carbon\Carbon::parse($trip->end_time)->format('d.m.Y H:i') }}
                                </div>

                                <div><strong>Price:</strong> {{ number_format($trip->price_per_seat, 0, '.', ' ') }} so'm</div>
                                <div><strong>Seats:</strong> {{ $trip->available_seats }} / {{ $trip->total_seats }}</div>

                                <div><strong>Status:</strong>
                                    <span class="badge {{ $trip->status === 'expired' ? 'bg-danger' : 'bg-success' }}">
                                        {{ ucfirst($trip->status) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No Trips Available</p>
                        @endforelse
                    </td>
                </tr>

                {{-- Delete Modal --}}
                <div class="modal fade" id="deleteModal{{ $driver->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content shadow">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">Delete Driver</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete <strong>{{ $driver->name }}</strong>?
                            </div>
                            <div class="modal-footer">
                                <form action="{{ route('drivers.destroy', $driver->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pay Modal --}}
                <div class="modal fade" id="pay{{ $driver->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content shadow">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Confirm Payment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to reset balance for <strong>{{ $driver->name }}</strong>?
                            </div>
                            <div class="modal-footer">
                                <form action="{{ route('drivers.reset-balance', $driver->id) }}" method="POST">
                                    @csrf
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Yes, Pay</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No drivers found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $drivers->links() }}
    </div>
</div>

{{-- FontAwesome --}}
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
@endsection
