<?php /** Reusable address fields for the checkout "new address" block. */ ?>
<div class="row g-2">
    <div class="col-md-8">
        <div class="form-outline <?= has_error('full_name') ? 'has-error' : '' ?>">
            <input type="text" name="full_name" class="form-control" value="<?= e(old('full_name')) ?>">
            <label class="form-label">Full name</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline <?= has_error('phone') ? 'has-error' : '' ?>">
            <input type="tel" name="phone" class="form-control" value="<?= e(old('phone')) ?>">
            <label class="form-label">Phone</label>
        </div>
    </div>
    <div class="col-12">
        <div class="form-outline <?= has_error('address_line1') ? 'has-error' : '' ?>">
            <input type="text" name="address_line1" class="form-control" value="<?= e(old('address_line1')) ?>">
            <label class="form-label">Street address</label>
        </div>
    </div>
    <div class="col-12">
        <div class="form-outline">
            <input type="text" name="address_line2" class="form-control" value="<?= e(old('address_line2')) ?>">
            <label class="form-label">Apartment, suite, etc. (optional)</label>
        </div>
    </div>
    <div class="col-md-5">
        <div class="form-outline <?= has_error('city') ? 'has-error' : '' ?>">
            <input type="text" name="city" class="form-control" value="<?= e(old('city')) ?>">
            <label class="form-label">City</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline">
            <input type="text" name="district" class="form-control" value="<?= e(old('district')) ?>">
            <label class="form-label">District</label>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-outline">
            <input type="text" name="postal_code" class="form-control" value="<?= e(old('postal_code')) ?>">
            <label class="form-label">Postal code</label>
        </div>
    </div>
</div>
