@extends('layouts.app')

@section('title', 'E\'lon')

@section('content')

@php
    $audienceLabels = ['all' => 'Hammaga', 'driver' => 'Haydovchilar', 'client' => 'Mijozlar'];
    $statusBadges = [
        'pending' => 'bg-secondary', 'sending' => 'bg-info text-dark',
        'sent' => 'bg-success', 'failed' => 'bg-danger',
    ];
@endphp

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <a href="{{ route('broadcasts.index') }}" class="btn btn-light rounded-pill mb-3">← Orqaga</a>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0">{{ $broadcast->title ?: 'E\'lon #'.$broadcast->id }}</h4>
                    <span class="badge {{ $statusBadges[$broadcast->status] ?? 'bg-secondary' }}">
                        {{ ucfirst($broadcast->status) }}
                    </span>
                </div>
                <div class="card-body">
                    @php
                        $langLabels = ['uz' => '🇺🇿 O\'zbekcha', 'ru' => '🇷🇺 Ruscha', 'en' => '🇬🇧 Inglizcha'];
                        $translations = $broadcast->translations ?: ['uz' => ['title' => $broadcast->title, 'body' => $broadcast->body]];
                    @endphp

                    @foreach($translations as $code => $t)
                        <div class="mb-3">
                            <span class="badge bg-light text-dark border mb-1">{{ $langLabels[$code] ?? $code }}</span>
                            @if(!empty($t['title']))
                                <div class="fw-semibold">{{ $t['title'] }}</div>
                            @endif
                            <div class="fs-6">{{ $t['body'] }}</div>
                        </div>
                    @endforeach

                    <hr>

                    <ul class="list-unstyled text-muted small mb-0">
                        <li><strong>Kimga:</strong> {{ $audienceLabels[$broadcast->audience] ?? $broadcast->audience }}</li>
                        <li><strong>Yuborilgan:</strong> {{ $broadcast->sent_count }} / {{ $broadcast->recipients_count }} qurilma</li>
                        <li><strong>Yuboruvchi:</strong> {{ optional($broadcast->sender)->first_name ?? '—' }}</li>
                        <li><strong>Sana:</strong> {{ $broadcast->created_at->format('d M, Y H:i') }}</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
