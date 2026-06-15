@extends('layouts.app')

@section('content')

<div class="container py-4 mt-4">

   <div class="card shadow rounded-4 p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">

        <h2>Vehicles</h2>

        <a href="{{ route('vehicles.create') }}"
           class="btn btn-primary">
            Add Vehicle
        </a>

    </div>
   </div>

    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif

    <div class="card">

        <div class="card-body p-0">

            <table class="table table-bordered table-hover mb-0">

                <thead class="table-dark">

                    <tr>

                        <th>ID</th>
                        <th>Driver</th>
                        <th>Expire Date of Driving Licence</th>
                        <th>Model</th>
                        <th>Car Number</th>
                        <th>Passport</th>
                        <th>Seats</th>
                        <th>Status</th>
                        <th width="180">Action</th>

                    </tr>

                </thead>

                <tbody>

                @forelse($vehicles as $vehicle)

                    <tr>

                        <td>
                            {{ $vehicle->id }}
                        </td>

                        <td>

                            {{ $vehicle->user->first_name ?? '-' }}
                            {{ $vehicle->user->last_name ?? '' }}

                            <br>

                            <small class="text-muted">
                                {{ $vehicle->user->phone ?? '' }}
                            </small>

                        </td>

                        <td>

                            <strong>

                                {{ $vehicle->user->driving_licence_expiry ?? '-' }}

                            </strong>

                        </td>

                        <td>

                            {{ $vehicle->model }}

                        </td>

                        <td>

                            <strong>

                                {{ $vehicle->car_number }}

                            </strong>

                        </td>

                        <td>

                            {{ $vehicle->tech_passport_number }}

                        </td>

                        <td>

                            {{ $vehicle->seats }}

                        </td>

                        <td>

                            @if($vehicle->status == 'active' || $vehicle->status == 1)

                                <span class="badge bg-success">
                                    Active
                                </span>

                            @else

                                <span class="badge bg-danger">
                                    Inactive
                                </span>

                            @endif

                        </td>

                        <td>

                            <div class="d-flex gap-1">

                                <a href="{{ route('vehicles.edit', $vehicle->id) }}"
                                   class="btn btn-warning btn-sm">

                                    Edit

                                </a>

                                <form action="{{ route('vehicles.destroy', $vehicle->id) }}"
                                      method="POST">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Delete vehicle?')">

                                        Delete

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="9"
                            class="text-center py-4">

                            No vehicles found

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <div class="mt-3">

        {{ $vehicles->links() }}

    </div>

</div>

@endsection