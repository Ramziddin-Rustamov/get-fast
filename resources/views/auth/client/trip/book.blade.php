@extends('layouts.app')

@section('content')
<div class="container mt-2">
    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-check-circle"></i> Success:</strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-times-circle"></i> Error:</strong> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-exclamation-triangle"></i> Validation Error:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        <div class="col-md-6">
            <div class="card mt-2 shadow-sm rounded-3">
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

                    <div class="mt-2 text-success">
                        <small><i class="fas fa-check-circle"></i>{{__('Available')}}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mt-2 shadow-sm rounded-3">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-user"></i> {{ __('Your Information') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('client.trips.book.post') }}" method="POST" class="needs-validation" novalidate>
                        @csrf 
                        <input type="hidden" name="trip_id" value="{{ $trip->id }}">
                        <input type="hidden" name="user_id" value="{{Auth::user()->id }}">

                        <div class="mb-3">
                            <label for="card_number" class="form-label">{{__('How many seats do you want? ')}}</label>
                            <input type="number" class="form-control" maxlength="1" minlength="1" id="seats" max="{{ $trip->available_seats }}" min="1" name="seats" placeholder="Seats" required>
                            <div class="invalid-feedback">
                                Please provide a valid card number.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="card_number" class="form-label">{{__('Phone for driver to contact')}}</label>
                            <input type="text" class="form-control" id="phone" name="extra_phone" placeholder="Phone" required>
                            <div class="invalid-feedback">
                                Please provide a valid card number.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="card_number" class="form-label">Card Number</label> 
                            <input type="text" class="form-control" id="card_number" name="card_number" pattern="\d{4}-\d{4}-\d{4}-\d{4}" maxlength="16" minlength="16" placeholder="0000-0000-0000-0000" required>
                            <div class="invalid-feedback">
                                Please provide a valid card number.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="cvv" class="form-label">{{__('Expiry month')}}</label>
                            <input type="number" class="form-control" maxlength="2" minlength="2" id="expiry_month" max="12" min="1" name="expiry_month" placeholder="MM" required>
                            <div class="invalid-feedback">
                                Please provide a valid expiry month.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cvv" class="form-label">{{__('Expiry year')}}</label>
                            <input type="number" class="form-control" maxlength="4" minlength="4" id="expiry_year" max="2040" min="2025" name="expiry_year" placeholder="YYYY" required>
                            <div class="invalid-feedback">
                                Please provide a valid expiry year.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cvv" maxlength="3" minlength="3" max="999" min="100" name="cvv" placeholder="CVV" required>
                            <div class="invalid-feedback">
                                Please provide a valid CVV.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Book Now</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection