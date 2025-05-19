<div class="mb-3">
    <label class="form-label">Card Number</label>
    <input type="text" class="form-control" name="card_number"
        value="{{ old('card_number', $card->card_number ?? '') }}" maxlength="16"
        placeholder="0000-0000-0000-0000" required >
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Expiry Month</label>
        <input type="number" class="form-control" name="expiry_month"
            value="{{ old('expiry_month', $card->expiry_month ?? '') }}" min="1" max="12" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Expiry Year</label>
        <input type="number" class="form-control" name="expiry_year"
            value="{{ old('expiry_year', $card->expiry_year ?? '') }}" min="{{ now()->year }}" max="2040" required>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">CVV</label>
    <input type="text" class="form-control" name="cvv" minlength="3" maxlength="3" value="{{ old('cvv', $card->cvv ?? '') }}" required>
</div>
