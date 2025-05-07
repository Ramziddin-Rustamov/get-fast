@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-12">
        <div class="card shadow p-3">
          <div class="card-header">{{ __('Edit Profile') }}</div>

          @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          @if ($errors->any())
            <div class="alert alert-danger">
              <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <div class="card-body">
            <form action="{{ route('profile.update.client', $client->id) }}" method="POST">
              @csrf
              @method('PUT')
              


              <!-- Region (Viloyat) -->
              <div class="row mb-3">
                <label for="region" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>
                <div class="col-md-6">
                
                  <input type="text" class="form-control" id="name" name="name" 
                    value="{{ old('name', $client->name) }}" required>
                </div>
              </div>

              <!-- Region (Viloyat) -->
              <div class="row mb-3">
                <label for="region" class="col-md-4 col-form-label text-md-end">{{ __('Region') }}</label>
                <div class="col-md-6">
                  <select id="region" name="region_id" class="form-control" required>
                    <option value="">{{__('Choose a region')}}</option>
                    @foreach ($regions as $region)
                      <option value="{{ $region->id }}" 
                        {{ old('region_id', $client->region_id) == $region->id ? 'selected' : '' }}>
                        {{ $region->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <!-- District (Tuman) -->
              <div class="row mb-3">
                <label for="district" class="col-md-4 col-form-label text-md-end">{{ __('District') }}</label>
                <div class="col-md-6">
                  <select id="district" class="form-control" name="district_id" required>
                    @if ($client->district_id)
                      <option value="{{ $client->district_id }}" selected>{{ $client->district->name }}</option>
                    @endif
                    <option value="">{{__('Choose a region first')}}</option>
                </select>
                </div>
              </div>

              <!-- Quarter (Mahalla) -->
              <div class="row mb-3">
                <label for="quarter" class="col-md-4 col-form-label text-md-end">{{ __('Village') }}</label>
                <div class="col-md-6">
                  <select id="quarter" class="form-control" name="quarter_id" required>
                    @if ($client->quarter_id)
                      <option value="{{ $client->quarter_id }}" selected>{{ $client->quarter->name }}</option>
                    @endif
                    <option disabled>{{__('Choose a district first')}}</option>
                </select>
                </div>
              </div>

              <!-- Home Address -->
              <div class="row mb-3">
                <label for="home" class="col-md-4 col-form-label text-md-end">{{ __('Home Address') }}</label>
                <div class="col-md-6">
                  <input id="home" type="text" class="form-control @error('home') is-invalid @enderror" 
                    name="home" value="{{ old('home', $client->home) }}" required 
                    autocomplete="home" placeholder="Masalan: Toshkent shahar, Chilonzor ko‘chasi 12">
                </div>
              </div>

              <button type="submit" class="btn btn-primary">{{__('Update')}}</button>
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