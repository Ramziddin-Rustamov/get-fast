@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Moshiangizni ruyxatdan o\'tqazing !') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('driver.auth.register.vehicle') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Kim tomonidan ishlab chiqarilgan ?') }}</label>

                            <div class="col-md-6">
                                <input id="make" type="text" class="form-control @error('make') is-invalid @enderror" name="make" value="{{ old('make') }}" required autocomplete="make" autofocus placeholder="GM">

                                @error('make')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        

                        <div class="row mb-3">
                            <label for="model" class="col-md-4 col-form-label text-md-end">{{ __('Nomi ? ') }}</label>

                            <div class="col-md-6">
                                <input id="model" type="text" class="form-control @error('model') is-invalid
                                 @enderror" name="model" value="{{ old('model') }}" required autocomplete="model" placeholder="Malibu">

                                @error('model')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Qachon ishlab chiqarilgan ?') }}</label>

                            <div class="col-md-6">
                                <input id="year" type="text" class="form-control @error('year') 
                                is-invalid @enderror" name="year" value="{{ old('year') }}" required autocomplete="year" 
                                autofocus placeholder="2020">

                                @error('year')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Moshina passport seriyasi ?') }}</label>

                            <div class="col-md-6">
                                <input id="license_plate" type="text" class="form-control @error('license_plate') 
                                is-invalid @enderror" name="license_plate" value="{{ old('license_plate') }}" 
                                required autocomplete="license_plate" autofocus placeholder="AA-1234">

                                @error('license_plate')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('O\'rindiqlar soni ? [haydavchidan tashqari]') }}</label>

                            <div class="col-md-6">
                                <input id="seats" type="text" class="form-control @error('seats') 
                                is-invalid @enderror" name="seats" value="{{ old('seats') }}"
                                 required autocomplete="seats" autofocus placeholder="4">

                                @error('seats')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>


                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Ruyxatdan o\'tqazing !') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
