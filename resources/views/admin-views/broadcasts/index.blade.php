@extends('layouts.app')

@section('title', 'E\'lonlar')

@section('content')

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark mb-0">📢 E'lonlar (Push)</h3>
                <a href="{{ route('broadcasts.create') }}" class="btn btn-primary rounded-pill">
                    ➕ Yangi e'lon
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success rounded-3 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @php
                $audienceLabels = ['all' => 'Hammaga', 'driver' => 'Haydovchilar', 'client' => 'Mijozlar'];
                $statusBadges = [
                    'pending' => 'bg-secondary', 'sending' => 'bg-info text-dark',
                    'sent' => 'bg-success', 'failed' => 'bg-danger',
                ];
            @endphp

            @forelse($broadcasts as $broadcast)
                <div class="card mb-3 shadow-sm rounded-4 border-0">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white border-0">
                        <strong class="text-primary">
                            {{ $broadcast->title ?: 'E\'lon #'.$broadcast->id }}
                        </strong>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark border">
                                {{ $audienceLabels[$broadcast->audience] ?? $broadcast->audience }}
                            </span>
                            <span class="badge {{ $statusBadges[$broadcast->status] ?? 'bg-secondary' }}">
                                {{ ucfirst($broadcast->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            {{ $broadcast->created_at->format('d M, Y H:i') }}
                            &middot; {{ $broadcast->sent_count }}/{{ $broadcast->recipients_count }} yuborildi
                        </p>
                        <p class="mb-3">{{ \Illuminate\Support\Str::limit($broadcast->body, 160) }}</p>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('broadcasts.show', $broadcast->id) }}"
                               class="btn btn-outline-primary btn-sm rounded-pill">🔍 Ko'rish</a>
                            <form method="POST" action="{{ route('broadcasts.destroy', $broadcast->id) }}"
                                  onsubmit="return confirm('E\'lonni o\'chirasizmi?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm rounded-pill">🗑 O'chirish</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info rounded-3">Hozircha e'lon yo'q.</div>
            @endforelse

            <div class="mt-4 d-flex justify-content-center">
                {{ $broadcasts->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>

@endsection
