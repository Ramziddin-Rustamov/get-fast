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
                        <!-- Region (Viloyat) -->
                        <div class="row mb-3">
                            <label for="region" class="col-md-4 col-form-label text-md-end">{{ __('Viloyat') }}</label>
                            <div class="col-md-6">
                                <select id="region" name="region_id" class="form-control" required>
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- District (Tuman) -->
                        <div class="row mb-3">
                            <label for="district" class="col-md-4 col-form-label text-md-end">{{ __('Tuman') }}</label>
                            <div class="col-md-6">
                                <select id="district" class="form-control" name="district_id" required>
                                    <option value="">Avval viloyatni tanlang</option>
                                </select>
                            </div>
                        </div>

                        <!-- Quarter (Mahalla) -->
                        <div class="row mb-3">
                            <label for="quarter" class="col-md-4 col-form-label text-md-end">{{ __('Mahalla') }}</label>
                            <div class="col-md-6">
                                <select id="quarter" class="form-control" name="quarter_id" required>
                                    <option value="">Avval tuman tanlang</option>
                                </select>
                            </div>
                        </div>

                            <!-- Quarter (home) -->
                            <div class="row mb-3">
                            <label for="quarter" class="col-md-4 col-form-label text-md-end">{{ __('Uy manzilingiz') }}</label>
                            <div class="col-md-6">
                                <input id="home" type="text" class="form-control
                                    @error('home') is-invalid @enderror" name="home" value="{{ old('home') }}" 
                                    required autocomplete="home" placeholder="Masalan Toshkent shahar, Chilonzor ko‘chasi 12">
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

@section('scripts')
<script>
      var appUrl = "{{ config('app.url') }}";  
$(document).ready(function() {
    // Region change event
    $('#region').on('change', function() {
        var regionId = $(this).val();  // Get the selected region ID
        if (regionId) {
            $.ajax({
                url:  appUrl + '/api/v1/districts/region/' + regionId,   
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log(data);  // Check the data structure
                    $('#district').empty();  // Clear existing options
                    $('#district').append('<option value="">Avval tuman tanlang</option>');  // Add default option
                    // Populate the district dropdown
                    $.each(data.data, function(index, district) {
                        $('#district').append('<option value="' + district.id + '">' + district.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.log(error);  // Log any errors
                }
            });
        } else {
            $('#district').empty();  // Clear district dropdown if no region is selected
            $('#district').append('<option value="">Avval viloyat tanlang</option>');
        }
    });

    // District change event
    $('#district').on('change', function() {
        var districtId = $(this).val();
        if (districtId) {
            $.ajax({
                url: appUrl + '/api/v1/quarters/districts/' + districtId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#quarter').empty();
                    $('#quarter').append('<option value="">Avval mahalla tanlang</option>');
                    $.each(data.data, function(index, quarter) {  // Use data.data to loop
                        $('#quarter').append('<option value="' + quarter.id + '">' + quarter.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.log(error);  // Log any errors
                }
            });
        } else {
            $('#quarter').empty();
            $('#quarter').append('<option value="">Avval tuman tanlang</option>');
        }
    });
});

</script>
@endsection