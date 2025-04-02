@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Moshiangizni ruyxatdan o\'tqazing !') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('driver.edit.vehicle.post') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $vehicle->id }}">
                        <div class="row mb-3">
                            <label for="make" class="col-md-4 col-form-label text-md-end">{{ __('Kim tomonidan ishlab chiqarilgan ?') }}</label>

                            <div class="col-md-6">
                                <input id="make" type="text" class="form-control @error('make') is-invalid @enderror" name="make" value="{{ $vehicle->make }}" required autocomplete="make" autofocus placeholder="GM">
                                @error('make')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="model" class="col-md-4 col-form-label text-md-end">{{ __('Car model') }}</label>

                            <div class="col-md-6">
                                <input id="model" type="text" class="form-control @error('model') is-invalid @enderror" name="model" value="{{ $vehicle->model }}" required autocomplete="model" placeholder="Malibu">
                                @error('model')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="year" class="col-md-4 col-form-label text-md-end">{{ __('When is it produced ?') }}</label>

                            <div class="col-md-6">
                                <input id="year" type="text" class="form-control @error('year') is-invalid @enderror" name="year" value="{{ $vehicle->year }}" required autocomplete="year" placeholder="2020">
                                @error('year')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="seats" class="col-md-4 col-form-label text-md-end">{{ __('O\'rindiqlar soni ? [haydavchidan tashqari]') }}</label>

                            <div class="col-md-6">
                                <input id="seats" type="text" class="form-control @error('seats') is-invalid @enderror" name="seats" value="{{ $vehicle->seats }}" required autocomplete="seats" placeholder="4">
                                @error('seats')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Save changes') }}
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
