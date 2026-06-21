@extends('layouts.app')

@section('title', 'Edit Client')

@push('styles')
<style>
    .k-page { max-width: 760px; }
    .k-hero {
        background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2));
        color: #fff; border-radius: 20px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 24px 50px -24px rgba(14,165,233,.6);
    }
    .k-hero h1 { font-size: 1.5rem; margin: 0; color: #fff; }
    .k-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 18px;
        box-shadow: 0 18px 40px -28px rgba(11,19,36,.45);
    }
    .k-card .k-card-body { padding: 1.5rem; }
    .sec-label { font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; font-weight: 700; margin: 1rem 0 .6rem; }
</style>
@endpush

@section('content')
<div class="container k-page py-4">

    {{-- Hero --}}
    <div class="k-hero d-flex align-items-center gap-3 mb-4">
        <div class="me-auto">
            <h1><i class="fas fa-user-pen me-2"></i> Mijozni tahrirlash</h1>
            <div class="mt-1 opacity-75">{{ $client->first_name }} {{ $client->last_name }}</div>
        </div>
        <a href="{{ route('clients.show', $client->id) }}" class="btn btn-light fw-bold rounded-3 px-3">
            <i class="fas fa-arrow-left me-1"></i> Orqaga
        </a>
    </div>

    <div class="k-card">
        <div class="k-card-body">
            @if ($errors->any())
                <div class="alert alert-danger rounded-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('clients.update', $client->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Shaxsiy ma'lumotlar --}}
                <p class="sec-label">Shaxsiy ma'lumotlar</p>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">Ism <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" id="first_name" class="form-control"
                               value="{{ old('first_name', $client->first_name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Familya</label>
                        <input type="text" name="last_name" id="last_name" class="form-control"
                               value="{{ old('last_name', $client->last_name) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="father_name" class="form-label">Otasining ismi</label>
                        <input type="text" name="father_name" id="father_name" class="form-control"
                               value="{{ old('father_name', $client->father_name) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control"
                               value="{{ old('email', $client->email) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Telefon <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="phone" class="form-control"
                               value="{{ old('phone', $client->phone) }}" required>
                    </div>
                </div>

                {{-- Manzil --}}
                <p class="sec-label">Manzil</p>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="region_id" class="form-label">Viloyat</label>
                        <select name="region_id" id="region_id" class="form-select"
                                data-selected="{{ old('region_id', $client->region_id) }}">
                            <option value="">— Tanlang —</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}"
                                    {{ (string)old('region_id', $client->region_id) === (string)$region->id ? 'selected' : '' }}>
                                    {{ $region->name_uz }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="district_id" class="form-label">Tuman</label>
                        <select name="district_id" id="district_id" class="form-select"
                                data-selected="{{ old('district_id', $client->district_id) }}">
                            <option value="">— Tanlang —</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="quarter_id" class="form-label">Mahalla</label>
                        <select name="quarter_id" id="quarter_id" class="form-select"
                                data-selected="{{ old('quarter_id', $client->quarter_id) }}">
                            <option value="">— Tanlang —</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="home" class="form-label">Uy / manzil</label>
                        <input type="text" name="home" id="home" class="form-control"
                               value="{{ old('home', $client->home) }}" placeholder="Ko‘cha, uy raqami...">
                    </div>
                </div>

                {{-- Holat --}}
                <p class="sec-label">Holat</p>
                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="is_verified" name="is_verified" value="1"
                           {{ old('is_verified', $client->is_verified) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_verified">Verified (SMS tasdiqlangan)</label>
                </div>

                <button type="submit" class="btn btn-success rounded-3 w-100">
                    <i class="fas fa-floppy-disk me-1"></i> Saqlash
                </button>
            </form>
        </div>
    </div>

</div>

<script>
    const regionSel   = document.getElementById('region_id');
    const districtSel = document.getElementById('district_id');
    const quarterSel  = document.getElementById('quarter_id');

    function fill(select, items, selected, label) {
        select.innerHTML = '<option value="">— Tanlang —</option>';
        items.forEach(function (it) {
            const opt = document.createElement('option');
            opt.value = it.id;
            opt.textContent = label(it);
            if (String(selected) === String(it.id)) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function loadDistricts(regionId, selected) {
        if (!regionId) { districtSel.innerHTML = '<option value="">— Tanlang —</option>'; return Promise.resolve(); }
        return fetch('/api/v1/districts/region/' + regionId)
            .then(r => r.json())
            .then(j => fill(districtSel, j.data || j, selected, it => it.name_uz));
    }

    function loadQuarters(districtId, selected) {
        if (!districtId) { quarterSel.innerHTML = '<option value="">— Tanlang —</option>'; return Promise.resolve(); }
        return fetch('/api/v1/quarters/districts/' + districtId)
            .then(r => r.json())
            .then(j => fill(quarterSel, j.data || j, selected, it => it.name));
    }

    // Initial load (preselect existing values)
    const initRegion   = regionSel.dataset.selected;
    const initDistrict = districtSel.dataset.selected;
    const initQuarter  = quarterSel.dataset.selected;

    if (initRegion) {
        loadDistricts(initRegion, initDistrict).then(function () {
            if (initDistrict) loadQuarters(initDistrict, initQuarter);
        });
    }

    regionSel.addEventListener('change', function () {
        quarterSel.innerHTML = '<option value="">— Tanlang —</option>';
        loadDistricts(this.value, null);
    });

    districtSel.addEventListener('change', function () {
        loadQuarters(this.value, null);
    });
</script>
@endsection
