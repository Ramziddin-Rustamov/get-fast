@extends('layouts.app')

@section('title', 'Support Messages')

@section('content')

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <h3 class="mb-4 fw-bold text-dark">📩 Support Messages</h3>

            @if(session('success'))
                <div class="alert alert-success rounded-3 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @foreach($messages as $message)
                <div class="card mb-3 shadow-sm rounded-4 border-0">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white border-0">
                        <div>
                            <strong class="text-primary">{{ $message->name }}</strong>  
                            <span class="text-muted">({{ $message->email }})</span>
                        </div>
                        <span class="badge 
                            {{ $message->status == 'answered' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ ucfirst($message->status) }}
                        </span>
                    </div>

                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            Sent on: {{ $message->created_at->format('d M, Y H:i') }}
                        </p>

                        <p class="fw-normal mb-3">
                            {{ $message->message }}
                        </p>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('support.show', $message->id) }}" 
                               class="btn btn-outline-primary btn-sm rounded-pill">
                                🔍 View
                            </a>

                            @if($message->status !== 'answered')
                                <form method="POST" action="{{ route('support.MarkAsAnswered', $message->id) }}">
                                    @csrf
                                    <button class="btn btn-success btn-sm rounded-pill">
                                        ✅ Mark Answered
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('support.destroy', $message->id) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm rounded-pill">
                                    🗑 Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="mt-4 d-flex justify-content-center">
                {{ $messages->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>

@endsection