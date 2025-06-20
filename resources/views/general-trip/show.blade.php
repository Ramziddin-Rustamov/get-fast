@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        <!-- Trip Information -->
        <div class="col-md-8">
            <div class="card mt-4 shadow-sm rounded-3">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-route"></i> {{ __('Trip Information') }}
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="text-muted"><i class="fas fa-map-marker-alt text-danger"></i> Trip From</h5>
                        <p class="mb-1">
                            @php
                            use Carbon\Carbon;
                        
                            $months = [
                                '01' => 'Yanvar',
                                '02' => 'Fevral',
                                '03' => 'Mart',
                                '04' => 'Aprel',
                                '05' => 'May',
                                '06' => 'Iyun',
                                '07' => 'Iyul',
                                '08' => 'Avgust',
                                '09' => 'Sentyabr',
                                '10' => 'Oktyabr',
                                '11' => 'Noyabr',
                                '12' => 'Dekabr',
                            ];
                        
                            $start = Carbon::parse($trip->start_time);
                            $day = $start->format('d');
                            $month = $months[$start->format('m')];
                            $year = $start->format('Y');
                            $time = $start->format('H:i');
                        @endphp
                        
                        <i class="far fa-clock"></i> 
                        <strong>{{ intval($day) }} {{ $month }} {{ $year }}, {{ $time }}</strong>
                        
                        </p>
                        <p>
                            {{ $trip->startQuarter->district->region->name }}
                            <i class="fas fa-arrow-right mx-1"></i>
                            {{ $trip->startQuarter->district->name }}
                            <i class="fas fa-arrow-right mx-1"></i>
                            <strong>{{ $trip->startQuarter->name }}</strong>
                        </p>
                    </div>

                    <!-- Duration -->
                    <div class="text-center mb-4">
                        <span class="badge bg-secondary">
                            <i class="fas fa-clock"></i>
                            Duration: {{ gmdate("H:i", strtotime($trip->end_time) - strtotime($trip->start_time)) }}
                        </span>
                    </div>

                    <div class="mb-4">
                        <h5 class="text-muted"><i class="fas fa-map-marker-alt text-success"></i> Trip To</h5>
                        <p class="mb-1">
                            @php
                        
                            $months = [
                                '01' => 'Yanvar',
                                '02' => 'Fevral',
                                '03' => 'Mart',
                                '04' => 'Aprel',
                                '05' => 'May',
                                '06' => 'Iyun',
                                '07' => 'Iyul',
                                '08' => 'Avgust',
                                '09' => 'Sentyabr',
                                '10' => 'Oktyabr',
                                '11' => 'Noyabr',
                                '12' => 'Dekabr',
                            ];
                        
                            $end = Carbon::parse($trip->end_time);
                            $endDay = $end->format('d');
                            $endMonth = $months[$end->format('m')];
                            $endYear = $end->format('Y');
                            $endTime = $end->format('H:i');
                        @endphp
                        
                        <i class="far fa-clock"></i>
                        <strong>{{ intval($endDay) }} {{ $endMonth }} {{ $endYear }}, {{ $endTime }}</strong>
                                                </p>
                        <p>
                            {{ $trip->endQuarter->district->region->name }}
                            <i class="fas fa-arrow-right mx-1"></i>
                            {{ $trip->endQuarter->district->name }}
                            <i class="fas fa-arrow-right mx-1"></i>
                            <strong>{{ $trip->endQuarter->name }}</strong>
                        </p>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-users"></i>
                            Seats: {{ $trip->available_seats }} / {{ $trip->total_seats }}
                        </span>
                        <span>
                            <i class="fas fa-coins text-warning"></i>
                            <strong>{{ number_format($trip->price_per_seat, 0, ',', ' ') }} UZS</strong>
                        </span>
                    </div>

                  @if($trip->available_seats > 0)

                    <div class="mt-2 text-success">
                        <small><i class="fas fa-check-circle"></i>{{__('Available')}}</small>
                    </div>

                    @else
                    <div class="mt-2 text-danger">
                        <small><i class="fas fa-times-circle"></i>{{__('Not Available')}}</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Driver Information -->
        
        <div class="col-md-4">
            <div class="card mt-4 shadow-sm rounded-3">
                <div class="card-header bg-info text-white fw-bold">
                    <i class="fas fa-user"></i> {{ __('Driver Information') }}
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img style="width: 50px; height: 50px;" src="{{ asset('image')}}/{{ $trip->driver->image }}" class="rounded-circle me-3" alt="Driver">
                        <div>
                            <div class="fw-bold">{{ $trip->driver->name }}</div>
                            <div class="text-muted small">
                                <i class="fas fa-map-marker-alt text-danger"></i>
                                {{ $trip->driver->quarter->name }},
                                {{ $trip->driver->quarter->district->name }},
                                {{ $trip->driver->quarter->district->region->name }}
                            </div>
                        </div>
                        <span class="ms-auto">
                            <i class="fas fa-star text-warning"></i> 5.0
                        </span>
                    </div>
        
                    <div class="alert alert-success p-2 small mb-2" role="alert">
                        <i class="fas fa-map-pin"></i> <strong>{{ __('Home') }}</strong><br>
                        {{ $trip->driver->quarter->name }},
                        {{ $trip->driver->quarter->district->name }},
                        {{ $trip->driver->quarter->district->region->name }}
                    </div>
                    @php
                    $phone = $trip->driver->phone;
                    $maskedPhone = substr($phone, 0, -5) . '****';
                @endphp
                
                <div>
                    <span><i class="fas fa-phone"></i> {{ $maskedPhone }}</span>
                </div>

                    <div class="mt-2">
                        <span class="text-muted"><i class="fas fa-shield-alt"></i> {{ __('No service fee.') }}</span>
                    </div>
                </div>
            </div>
   
            <div class="mt-1">
                @auth
                @if($trip->available_seats > 0)
                <a href="{{ route('trip.book', $trip->id) }}" class="btn btn-primary  hoverable w-100">{{ __('Book Trip') }}</a>
                @else
                <button class="btn btn-danger w-100" disabled>
                    <i class="fas fa-ban"></i> {{ __('No Seats Available') }}
                </button>
                @endif
                @else
                <a href="{{ route('auth.login.index') }}" class="btn btn-primary hoverable w-100">{{ __('Book Trip') }}</a>
                @endauth
            </div>
                      <!-- Parcel Information -->
                      @if($trip->parcels->count() > 0)
                      <div class="card mt-3 shadow-sm rounded-3">
                          <div class="card-header bg-warning text-white fw-bold">
                              <i class="fas fa-box"></i> {{ __('Parcel Information') }}
                          </div>
                          <div class="card-body">
                              <table class="table table-striped table-sm">
                                  <thead>
                                      <tr>
                                          <th scope="col"><i class="fas fa-box-open"></i> {{ __('Max Weight (kg)') }}</th>
                                          <th scope="col"><i class="fas fa-money-bill-wave"></i> {{ __('Price per Kg (UZS)') }}</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      @foreach ($trip->parcels as $parcel)
                                          <tr>
                                              <td>{{ number_format($parcel->max_weight, 0, ',', ' ') }}</td>
                                              <td>{{ number_format($parcel->price_per_kg, 0, ',', ' ') }}</td>
                                          </tr>
                                      @endforeach
                                  </tbody>
                              </table>
                              <div class="mt-2">
                                  <span class="text-muted"><i class="fas fa-shield-alt"></i> {{ __('No service fee.') }}</span>
                              </div>
                          </div>
                         
                      </div>
                      <div class="mt-1">
                        @auth
                        @if($parcel->max_weight > 0)
                                <a href="{{ route('client.parcel.show', $parcel->id) }}" class="btn btn-primary  hoverable w-100">{{ __('Book Parcel') }}</a>
                                @else
                                <a href="{{ route('client.parcel.show', $parcel->id) }}" class="btn btn-primary  hoverable w-100 disabled">{{ __('Full') }}</a>
                            @endif
                        @endauth
                    </div>
                  @endif
          </div>
    </div>
</div>
@endsection
