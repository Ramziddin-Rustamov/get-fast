@extends('layouts.app')

@section('title', 'Message Details')

@section('content')

<div class="container mt-5 pt-5">
    <div class="card shadow-lg border-0 rounded-4">
        
        <div class="card-header bg-white border-0 d-flex justify-content-between">
            <h4 class="fw-bold">📨 Message Detail</h4>

            <span class="badge 
                {{ $message->status == 'answered' ? 'bg-success' : 'bg-warning' }}">
                {{ $message->status }}
            </span>
        </div>

        <div class="card-body">

            <div class="mb-3">
                <strong>👤 Name:</strong> {{ $message->name }}
            </div>

            <div class="mb-3">
                <strong>📧 Email:</strong> {{ $message->email }}
            </div>

            <div class="mb-3">
                <strong>🕒 Date:</strong> {{ $message->created_at }}
            </div>

            <hr>

            <div class="mb-4">
                <strong>💬 Message:</strong>
                <div class="p-3 bg-light rounded-3 mt-2">
                    {{ $message->message }}
                </div>
            </div>

            @if($message->status !== 'answered')
                <form method="POST" action="{{ route('support.MarkAsAnswered', $message->id) }}">
                    @csrf
                    <button class="btn btn-success rounded-pill">
                        ✅ Mark as Answered
                    </button>
                </form>
            @endif

            <a href="{{ route('support.index') }}" class="btn btn-secondary mt-3 rounded-pill">
                ⬅ Back
            </a>

        </div>
    </div>
</div>

@endsection