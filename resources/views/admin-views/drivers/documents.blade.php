@extends('layouts.app')

@section('title', 'Driver Documents')

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

    .k-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 18px;
        box-shadow: 0 18px 40px -28px rgba(11,19,36,.45);
    }
    .k-card .k-card-head {
        display: flex; align-items: center; justify-content: space-between;
        gap: .75rem; flex-wrap: wrap;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .k-card .k-card-body { padding: 1.25rem; }
    .k-title { display: flex; align-items: center; gap: .6rem; font-size: 1.05rem; font-weight: 700; margin: 0; }
    .k-chip {
        width: 38px; height: 38px; border-radius: 11px;
        display: grid; place-items: center; color: #fff; font-size: .95rem;
        background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
    }

    .doc-tile { border: 1px solid #eef2f7; border-radius: 14px; padding: .75rem; text-align: center; height: 100%; }
    .doc-preview { cursor: zoom-in; object-fit: cover; border-radius: 10px; }
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
            <h1><i class="fas fa-file-lines me-2"></i> {{ $driver->first_name }} {{ $driver->last_name }} — Hujjatlari</h1>
            <div class="mt-1 opacity-75">Jami: {{ $driverImages->count() }} ta hujjat</div>
        </div>
        <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-arrow-left me-1"></i> Haydovchiga qaytish
        </a>
    </div>

    <div class="k-card mb-4">
        <div class="k-card-head">
            <h2 class="k-title"><span class="k-chip"><i class="fas fa-file-lines"></i></span> Haydovchi Hujjatlari</h2>

            <form action="{{ route('driver.images.deleteAll', $driver->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm rounded-3"
                        onclick="return confirm('Hamma hujjatlar o‘chirilsinmi?')">
                    Hamma Hujjatlarni O‘chirish
                </button>
            </form>
        </div>
        <div class="k-card-body">
            @if($driverImages->count())
                <div class="row g-3">
                    @foreach($driverImages as $img)
                        <div class="col-md-4">
                            <div class="doc-tile">
                                <p class="fw-bold text-capitalize mb-2">
                                    {{ str_replace('_', ' ', $img->type) }}
                                    @if($img->side)
                                        ({{ ucfirst($img->side) }})
                                    @endif
                                </p>

                                <img src="{{ asset('storage/' . $img->image_path) }}"
                                     class="img-fluid shadow-sm doc-preview"
                                     style="max-height: 220px;"
                                     data-bs-toggle="modal"
                                     data-bs-target="#imageModal"
                                     data-img="{{ asset('storage/' . $img->image_path) }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0">Hujjatlar mavjud emas.</p>
            @endif
        </div>
    </div>

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
        if (e.target.matches('.doc-preview')) {
            document.getElementById('modalImage').src = e.target.dataset.img;
        }
    });
</script>
@endsection
