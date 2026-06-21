@extends('layouts.app')

@section('title', 'Add New Client')

@push('styles')
<style>
    .k-page { max-width: 680px; }
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
    .k-card .k-card-body { padding: 1.5rem; }
</style>
@endpush

@section('content')
<div class="container k-page py-4">

    {{-- Hero --}}
    <div class="k-hero d-flex align-items-center gap-3 mb-4">
        <div class="me-auto">
            <h1><i class="fas fa-user-plus me-2"></i> Yangi mijoz qo‘shish</h1>
        </div>
        <a href="{{ route('clients.index') }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-arrow-left me-1"></i> Ro‘yxatga qaytish
        </a>
    </div>

    <div class="k-card">
        <div class="k-card-body">
            @if ($errors->any())
                <div class="alert alert-danger rounded-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('clients.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">Ism <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" id="first_name" class="form-control"
                               value="{{ old('first_name') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Familya</label>
                        <input type="text" name="last_name" id="last_name" class="form-control"
                               value="{{ old('last_name') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Telefon <span class="text-danger">*</span></label>
                    <input type="text" name="phone" id="phone" class="form-control"
                           value="{{ old('phone') }}" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Parol <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Parolni tasdiqlang <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="is_verified" name="is_verified" value="1" checked>
                    <label class="form-check-label" for="is_verified">Verified (SMS tasdiqlangan)</label>
                </div>

                <button type="submit" class="btn btn-success rounded-3 w-100">
                    <i class="fas fa-plus-circle me-1"></i> Mijoz qo‘shish
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
