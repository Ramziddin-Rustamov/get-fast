@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row d-flex justify-content-center align-items-center vh-100">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header text-center">{{ __('Kirish') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('auth.login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="phone" class="form-label">Telefon raqam</label>
                            <input id="phone" type="text" name="phone" class="form-control" required value="{{ old('phone') }}">
                            @error('phone')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Parol</label>
                            <input id="password" type="password" name="password" class="form-control" required>
                            @error('password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Kirish</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
