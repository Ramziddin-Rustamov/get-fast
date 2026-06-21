@extends('layouts.app')

@section('title', 'Driver Vehicles')

@push('styles')
<style>
    .k-page { max-width: 1100px; }

    .k-hero {
        background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
        color: #fff; border-radius: 20px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 24px 50px -24px rgba(14,165,233,.6);
    }
    .k-hero h1 { font-size: 1.5rem; margin: 0; color: #fff; }

    .vehicle-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 40px -28px rgba(11,19,36,.45);
    }
    .vehicle-card-head {
        display: flex; justify-content: space-between; align-items: flex-start;
        gap: 1rem; flex-wrap: wrap;
        padding: 1.1rem 1.25rem;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f7;
    }
    .vehicle-title { font-family: 'Sora', sans-serif; font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: .6rem; }
    .vehicle-meta { display: flex; flex-wrap: wrap; gap: 1.25rem; margin-top: .5rem; color: #64748b; font-size: .9rem; }
    .vehicle-meta b { color: var(--k-ink); }

    .vehicle-card-body { padding: 1.25rem; }

    .img-tile { border: 1px solid #eef2f7; border-radius: 14px; padding: .6rem; text-align: center; height: 100%; }
    .vehicle-preview { cursor: zoom-in; object-fit: cover; border-radius: 10px; }

    .sec-label { font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; font-weight: 700; margin: 0; }
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
        <div class="me-auto">
            <h1><i class="fas fa-car me-2"></i> {{ $driver->first_name }} {{ $driver->last_name }} — Moshinalari</h1>
            <div class="mt-1 opacity-75">Jami: {{ $vehicles->total() }} ta moshina</div>
        </div>
        <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-arrow-left me-1"></i> Haydovchiga qaytish
        </a>
    </div>

    @forelse($vehicles as $vehicle)
        <div class="vehicle-card mb-4">

            {{-- Header --}}
            <div class="vehicle-card-head">
                <div>
                    <div class="vehicle-title">
                        <i class="fas fa-car text-primary"></i> {{ $vehicle->model }}
                    </div>
                    <div class="vehicle-meta">
                        <span><b>Rang:</b> {{ $vehicle->color->title_uz ?? '—' }}</span>
                        <span><b>O'rin:</b> {{ $vehicle->seats }}</span>
                        <span><b>Raqami:</b> {{ $vehicle->car_number }}</span>
                        <span><b>Tex passport:</b> {{ $vehicle->tech_passport_number }}</span>
                    </div>
                </div>
            </div>

            {{-- Body: images --}}
            @php
                $images = $vehicleImages->where('vehicle_id', $vehicle->id);
            @endphp
            <div class="vehicle-card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="sec-label"><i class="fas fa-images me-1"></i> Moshina Rasmlari ({{ $images->count() }})</p>

                    @if ($images->count())
                        <form action="{{ route('vehicle.images.deleteAll', $vehicle->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm rounded-3"
                                    onclick="return confirm('Hamma moshina rasmlari o‘chirilsinmi?')">
                                O‘chirish
                            </button>
                        </form>
                    @endif
                </div>

                @if ($images->count())
                    <div class="row g-3">
                        @foreach($images as $vimg)
                            <div class="col-md-3 col-6">
                                <div class="img-tile">
                                    <p class="fw-bold small mb-2">
                                        {{ str_replace('_', ' ', $vimg->type) }}
                                        @if($vimg->side)
                                            ({{ ucfirst($vimg->side) }})
                                        @endif
                                    </p>

                                    <img src="{{ asset('storage/' . $vimg->image_path) }}"
                                         class="img-fluid shadow-sm vehicle-preview"
                                         style="max-height: 160px;"
                                         data-bs-toggle="modal"
                                         data-bs-target="#imageModal"
                                         data-img="{{ asset('storage/' . $vimg->image_path) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">Rasmlar mavjud emas.</p>
                @endif
            </div>
        </div>
    @empty
        <div class="vehicle-card"><div class="vehicle-card-body"><p class="text-muted mb-0">Moshina biriktirilmagan.</p></div></div>
    @endforelse

    @if($vehicles->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $vehicles->links('pagination::bootstrap-5') }}
        </div>
    @endif

    {{-- Image preview Modal --}}
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 bg-transparent shadow-none">
                <div class="position-relative text-center">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2"
                            data-bs-dismiss="modal" style="z-index:2;"></button>
                    <img id="modalImage" src="" class="img-fluid rounded-4 shadow" alt="Preview">
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('click', function(e) {
        if (e.target.matches('.vehicle-preview')) {
            document.getElementById('modalImage').src = e.target.dataset.img;
        }
    });
</script>
@endsection
