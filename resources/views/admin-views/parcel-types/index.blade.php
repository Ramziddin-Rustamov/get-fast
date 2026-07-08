@extends('layouts.app')

@section('title', 'Pochta turlari')

@section('content')

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark mb-0">📦 Pochta turlari</h3>
                <a href="{{ route('parcel-types.create') }}" class="btn btn-primary rounded-pill">
                    ➕ Yangi tur
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success rounded-3 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Nomi (uz)</th>
                                    <th>Ruscha</th>
                                    <th>Inglizcha</th>
                                    <th>Ikonka</th>
                                    <th>Holati</th>
                                    <th class="text-end pe-4">Amallar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($parcelTypes as $type)
                                    <tr>
                                        <td class="ps-4 text-muted">{{ $type->id }}</td>
                                        <td class="fw-semibold">{{ $type->name_uz }}</td>
                                        <td>{{ $type->name_ru }}</td>
                                        <td>{{ $type->name_en }}</td>
                                        <td>
                                            @if($type->icon)
                                                <code>{{ $type->icon }}</code>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($type->is_active)
                                                <span class="badge bg-success">Faol</span>
                                            @else
                                                <span class="badge bg-secondary">Nofaol</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-inline-flex gap-2">
                                                <a href="{{ route('parcel-types.edit', $type->id) }}"
                                                   class="btn btn-outline-primary btn-sm rounded-pill">✏️ Tahrir</a>
                                                <form method="POST" action="{{ route('parcel-types.destroy', $type->id) }}"
                                                      onsubmit="return confirm('Turni o\'chirasizmi?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm rounded-pill">🗑</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Hozircha pochta turi yo'q.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $parcelTypes->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>

@endsection
