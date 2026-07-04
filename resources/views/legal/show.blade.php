@extends('layouts.app')

@section('title', $title)

@push('styles')
<style>
    .k-page { max-width: 860px; }
    .k-hero {
        background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
        color: #fff; border-radius: 20px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 24px 50px -24px rgba(14,165,233,.6);
    }
    .k-hero h1 { font-size: 1.6rem; margin: 0; color: #fff; }

    .lang-tabs .btn { font-weight: 700; border-radius: 999px; }

    .legal-card {
        background: #fff; border: 1px solid #eef2f7; border-radius: 18px;
        box-shadow: 0 18px 40px -28px rgba(11,19,36,.45);
        padding: 2rem 2.25rem;
    }
    .prose { color: #1f2937; line-height: 1.7; }
    .prose h1 { font-family: 'Sora', sans-serif; font-weight: 800; font-size: 1.7rem; margin: 0 0 .25rem; color: var(--k-ink); }
    .prose h2 { font-family: 'Sora', sans-serif; font-weight: 700; font-size: 1.2rem; margin: 1.8rem 0 .6rem; color: var(--k-ink); padding-top: .4rem; border-top: 1px solid #f1f5f9; }
    .prose h2:first-of-type { border-top: 0; }
    .prose p { margin: 0 0 .85rem; }
    .prose ul { margin: 0 0 1rem; padding-left: 1.25rem; }
    .prose li { margin-bottom: .35rem; }
    .prose hr { border: 0; border-top: 1px solid #e5e7eb; margin: 1.5rem 0; }
    .prose strong { color: var(--k-ink); }
    .prose em { color: #64748b; }
    .prose a { color: var(--k-acc-2); }
    @media (max-width: 575px){ .legal-card { padding: 1.25rem; } }
</style>
@endpush

@section('content')
<div class="container k-page py-4">

    {{-- Hero --}}
    <div class="k-hero d-flex align-items-center gap-3 flex-wrap mb-4">
        <div class="me-auto">
            <h1><i class="fas fa-file-shield me-2"></i> {{ $title }}</h1>
            @if($type !== 'rules')
                <div class="mt-2 lang-tabs d-flex gap-2">
                    @php $langs = ['uz' => "O'zbekcha", 'ru' => 'Русский', 'en' => 'English']; @endphp
                    @foreach($langs as $code => $name)
                        <a href="{{ route('legal.' . $type, ['lang' => $code]) }}"
                           class="btn btn-sm {{ $lang === $code ? 'btn-light' : 'btn-outline-light' }}">
                            {{ strtoupper($code) }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if($type !== 'rules')
            @php $other = $type === 'terms' ? 'privacy' : 'terms'; @endphp
            <a href="{{ route('legal.' . $other, ['lang' => $lang]) }}" class="btn btn-light fw-bold rounded-3 px-3">
                <i class="fas fa-arrow-right-arrow-left me-1"></i>
                {{ $type === 'terms' ? ($lang === 'ru' ? 'Конфиденциальность' : ($lang === 'en' ? 'Privacy' : 'Maxfiylik')) : ($lang === 'ru' ? 'Условия' : ($lang === 'en' ? 'Terms' : 'Shartlar')) }}
            </a>
        @endif
    </div>

    <div class="legal-card">
        <article class="prose">
            {!! $html !!}
        </article>
    </div>

</div>
@endsection
