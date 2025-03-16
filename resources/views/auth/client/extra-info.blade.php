@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Qoshimcha ma\'lumotlaringizni kiriting') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('client.auth.register.extra-info.post') }}">
                        @csrf

                        <!-- Region (Viloyat) -->
                        <div class="row mb-3">
                            <label for="region" class="col-md-4 col-form-label text-md-end">{{ __('Viloyat') }}</label>
                            <div class="col-md-6">
                                <select id="region" name="region" class="form-control" required>
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
                                    {{ __('Ro‘yxatdan o‘tish') }}
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

