@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded">
            <div class="card-header text-center bg-primary text-white">
                <h4>{{ __('Telefon raqamingizni tasdiqlang') }}</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('phone.verify') }}">
                    @csrf
                    <div class="mb-3 text-center">
                        <div class="py-3">
                            <label for="phone" class="form-label fw-bold">{{ __('Telefon raqam') }}</label>
                        <input type="text" disabled class="form-control text-center @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $phone ?? '') }}" required autofocus>
                        </div>

                        <div class="py-2">
                            <input type="text"  class="form-control text-center @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required autofocus placeholder="Kodni kiriting .">
                        </div>
                          @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">{{ __('Tasdiqlash') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

