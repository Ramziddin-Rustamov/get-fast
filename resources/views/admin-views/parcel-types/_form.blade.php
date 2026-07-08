@csrf

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label fw-semibold">Nomi (uz) <span class="text-danger">*</span></label>
        <input type="text" name="name_uz" value="{{ old('name_uz', $parcelType->name_uz ?? '') }}"
               class="form-control rounded-3" maxlength="255" required
               placeholder="Masalan: Hujjat / konvert">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Заголовок (ru) <span class="text-danger">*</span></label>
        <input type="text" name="name_ru" value="{{ old('name_ru', $parcelType->name_ru ?? '') }}"
               class="form-control rounded-3" maxlength="255" required
               placeholder="Например: Документы">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Name (en) <span class="text-danger">*</span></label>
        <input type="text" name="name_en" value="{{ old('name_en', $parcelType->name_en ?? '') }}"
               class="form-control rounded-3" maxlength="255" required
               placeholder="e.g. Documents">
    </div>
</div>

<div class="mb-3 mt-3">
    <label class="form-label fw-semibold">Ikonka nomi (ixtiyoriy)</label>
    <input type="text" name="icon" value="{{ old('icon', $parcelType->icon ?? '') }}"
           class="form-control rounded-3" maxlength="255"
           placeholder="masalan: document, box_small, food">
    <div class="form-text">Mobil ilova shu nom bo'yicha ikonkani ko'rsatadi.</div>
</div>

<div class="form-check form-switch mb-4">
    <input class="form-check-input" type="checkbox" role="switch" id="is_active"
           name="is_active" value="1"
           {{ old('is_active', $parcelType->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label fw-semibold" for="is_active">Faol (haydovchilarga ko'rsatilsin)</label>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary rounded-pill px-4">💾 Saqlash</button>
    <a href="{{ route('parcel-types.index') }}" class="btn btn-light rounded-pill px-4">Bekor qilish</a>
</div>
