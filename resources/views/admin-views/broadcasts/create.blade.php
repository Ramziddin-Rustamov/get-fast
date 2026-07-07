@extends('layouts.app')

@section('title', 'Yangi e\'lon')

@section('content')

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <h3 class="fw-bold text-dark mb-4">📢 Yangi e'lon yuborish</h3>

            @if($errors->any())
                <div class="alert alert-danger rounded-3">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('broadcasts.store') }}">
                        @csrf

                        <p class="text-muted small mb-2">
                            Har bir foydalanuvchi o'z tilida xabar oladi. <strong>O'zbekcha majburiy</strong>
                            (til topilmasa shu ishlatiladi). Rus/Ingliz — ixtiyoriy.
                        </p>

                        {{-- Til tablari --}}
                        <ul class="nav nav-pills mb-3" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-uz" type="button">🇺🇿 O'zbekcha *</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ru" type="button">🇷🇺 Ruscha</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-en" type="button">🇬🇧 Inglizcha</button></li>
                        </ul>

                        <div class="tab-content mb-3">
                            {{-- UZ --}}
                            <div class="tab-pane fade show active" id="tab-uz">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Sarlavha (uz)</label>
                                    <input type="text" name="title_uz" value="{{ old('title_uz') }}"
                                           class="form-control rounded-3" maxlength="255" placeholder="Masalan: Yangilik">
                                </div>
                                <div class="mb-1">
                                    <label class="form-label fw-semibold">Xabar matni (uz) <span class="text-danger">*</span></label>
                                    <textarea name="body_uz" rows="4" required maxlength="2000"
                                              class="form-control rounded-3"
                                              placeholder="Bugun qayerga borishni rejalashtiryapsiz?">{{ old('body_uz') }}</textarea>
                                </div>
                            </div>
                            {{-- RU --}}
                            <div class="tab-pane fade" id="tab-ru">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Заголовок (ru)</label>
                                    <input type="text" name="title_ru" value="{{ old('title_ru') }}"
                                           class="form-control rounded-3" maxlength="255" placeholder="Например: Новость">
                                </div>
                                <div class="mb-1">
                                    <label class="form-label fw-semibold">Текст сообщения (ru)</label>
                                    <textarea name="body_ru" rows="4" maxlength="2000"
                                              class="form-control rounded-3"
                                              placeholder="Куда вы планируете поехать сегодня?">{{ old('body_ru') }}</textarea>
                                </div>
                            </div>
                            {{-- EN --}}
                            <div class="tab-pane fade" id="tab-en">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Title (en)</label>
                                    <input type="text" name="title_en" value="{{ old('title_en') }}"
                                           class="form-control rounded-3" maxlength="255" placeholder="e.g. News">
                                </div>
                                <div class="mb-1">
                                    <label class="form-label fw-semibold">Message (en)</label>
                                    <textarea name="body_en" rows="4" maxlength="2000"
                                              class="form-control rounded-3"
                                              placeholder="Where are you planning to go today?">{{ old('body_en') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Kimga yuborilsin?</label>
                            <select name="audience" class="form-select rounded-3">
                                <option value="all" {{ old('audience') == 'all' ? 'selected' : '' }}>Hammaga</option>
                                <option value="driver" {{ old('audience') == 'driver' ? 'selected' : '' }}>Faqat haydovchilar</option>
                                <option value="client" {{ old('audience') == 'client' ? 'selected' : '' }}>Faqat mijozlar</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                🚀 Yuborish
                            </button>
                            <a href="{{ route('broadcasts.index') }}" class="btn btn-light rounded-pill px-4">
                                Bekor qilish
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
