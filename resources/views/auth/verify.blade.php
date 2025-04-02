@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row  d-flex justify-content-center align-items-center vh-100">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Kirish ') }}</div>

                <div class="card-body">
                    <form action="{{ route('auth.verify.post') }}" method="POST">
                        @csrf
                        <input type="hidden"  class="form-control" name="user_id" value="{{ $user_id }}">
                        <input type="hidden" class="form-control" name="phone" value="{{ $phone }}">
                        <input type="text" class="form-control mb-3" name="code" placeholder="Enter verification code">
                        <button class="btn btn-primary" type="submit"> Verify</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
