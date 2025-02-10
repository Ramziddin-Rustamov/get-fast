@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h4>{{ __('Ro‘yxatdan o‘tish') }}</h4>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3 form-group">
                            <label for="name" class="form-label">{{ __('Ismingiz to‘liq') }}</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                            @error('name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>


                        <div class="mb-3 form-group">
                            <label for="region_id" class="form-label">{{ __('Viloyat') }}</label>
                            <select class="form-control @error('region_id') is-invalid @enderror" id="region_id" name="region_id" required>
                                <option value="">Qaysi viloyatdansiz ?</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                                @endforeach
                            </select>
                            @error('region_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3 form-group">
                            <label for="district_id" class="form-label">{{ __('Tuman') }}</label>
                            <select class="form-control @error('district_id') is-invalid @enderror" id="district_id" name="district_id" required>
                                <option value="">Qaysi tumandansiz ?</option>
                            </select>
                            @error('district_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3 form-group">
                            <label for="quarter_id" class="form-label">{{ __('Mahalla ') }}</label>
                            <select class="form-control @error('quarter_id') is-invalid @enderror" id="quarter_id" name="quarter_id" required>
                                <option value="">Mahallangiz ?</option>
                            </select>
                            @error('quarter_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <input type="hidden" name="role" value="client">


                        <div class="mb-3 form-group">
                            <label for="home" class="form-label">{{ __('Uy raqami va kucha nomi ?') }}</label>
                            <input id="home" type="text" class="form-control @error('home') is-invalid @enderror" 
                                   name="home" value="{{ old('home') }}" required autocomplete="home    " autofocus>
                            @error('home')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3 form-group">
                            <label for="phone" class="form-label">{{ __('Telefon raqamingiz') }}</label>
                            <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   name="phone" value="{{ old('phone') }}" required autocomplete="phone">
                            @error('phone')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3 form-group">
                            <label for="password" class="form-label">{{ __('Parol') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                   name="password" required autocomplete="new-password">
                            @error('password')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3 form-group">
                            <label for="password-confirm" class="form-label">{{ __('Parolni qayta kiriting') }}</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-success w-100 py-2">
                                {{ __('Ro‘yxatdan o‘tish') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
  $(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#region_id').change(function () {
        var region_id = $(this).val();
        $('#district_id').empty().append('<option value="">Select District</option>');
        $('#quarter_id').empty().append('<option value="">Select Quarter</option>');

        if (region_id) {
            $.getJSON('/get-districts/' + region_id, function (data) {
                $.each(data, function (key, value) {
                    $('#district_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            }).fail(function () {
                console.log('Xatolik: Districtlarni yuklashda xatolik yuz berdi.');
            });
        }
    });

    $('#district_id').change(function () {
        var district_id = $(this).val();
        $('#quarter_id').empty().append('<option value="">Select Quarter</option>');

        if (district_id) {
            $.getJSON('/get-quarters/' + district_id, function (data) {
                $.each(data, function (key, value) {
                    $('#quarter_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            }).fail(function () {
                console.log('Xatolik: Quarterlarni yuklashda xatolik yuz berdi.');
            });
        }
    });
});
</script>
@endsection
