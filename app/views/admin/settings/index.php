<?php /** Site & gateway settings. Values read live from the settings table. */ ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Settings</h4>
</div>
<form method="post" action="<?= url('/admin/settings') ?>">
    <?= csrf_field() ?>

    <div class="card mb-3"><div class="card-body">
        <h6 class="mb-3">General</h6>
        <div class="row g-2">
            <div class="col-md-8"><div class="form-outline mb-2"><input type="text" name="site_name" class="form-control" value="<?= e(setting('site_name', APP_NAME)) ?>"><label class="form-label">Site name</label></div></div>
            <div class="col-md-2"><div class="form-outline mb-2"><input type="text" name="currency_code" class="form-control" value="<?= e(setting('currency_code','NPR')) ?>"><label class="form-label">Currency code</label></div></div>
            <div class="col-md-2"><div class="form-outline mb-2"><input type="text" name="currency_symbol" class="form-control" value="<?= e(setting('currency_symbol','रू')) ?>"><label class="form-label">Symbol</label></div></div>
        </div>
        <div class="form-outline mb-2"><input type="text" name="site_tagline" class="form-control" value="<?= e(setting('site_tagline','')) ?>"><label class="form-label">Tagline</label></div>
        <div class="row g-2">
            <div class="col-md-6"><div class="form-outline mb-0"><input type="email" name="contact_email" class="form-control" value="<?= e(setting('contact_email','')) ?>"><label class="form-label">Contact email</label></div></div>
            <div class="col-md-6"><div class="form-outline mb-0"><input type="text" name="contact_phone" class="form-control" value="<?= e(setting('contact_phone','')) ?>"><label class="form-label">Contact phone</label></div></div>
        </div>
    </div></div>

    <div class="card mb-3"><div class="card-body">
        <h6 class="mb-3">Commerce</h6>
        <div class="row g-2">
            <div class="col-md-4">
                <select name="approval_mode" class="form-select">
                    <option value="auto" <?= setting('approval_mode','auto')==='auto'?'selected':'' ?>>Auto-publish products</option>
                    <option value="pending" <?= setting('approval_mode','auto')==='pending'?'selected':'' ?>>Require admin approval</option>
                </select>
                <label class="form-label d-block">Product approval</label>
            </div>
            <div class="col-md-4"><div class="form-outline"><input type="number" step="0.01" name="default_commission_rate" class="form-control" value="<?= e(setting('default_commission_rate','10.00')) ?>"><label class="form-label">Default commission %</label></div></div>
            <div class="col-md-2"><div class="form-outline"><input type="number" step="0.01" name="free_shipping_threshold" class="form-control" value="<?= e(setting('free_shipping_threshold','2000')) ?>"><label class="form-label">Free ship over</label></div></div>
            <div class="col-md-2"><div class="form-outline"><input type="number" step="0.01" name="shipping_fee" class="form-control" value="<?= e(setting('shipping_fee','150')) ?>"><label class="form-label">Ship fee</label></div></div>
        </div>
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="review_auto_approve" value="1" id="raa" <?= setting('review_auto_approve','1')==='1'?'checked':'' ?>>
            <label class="form-check-label" for="raa">Auto-approve new customer reviews</label>
        </div>
    </div></div>

    <div class="card mb-3"><div class="card-body">
        <h6 class="mb-3">Payment gateways <small class="text-muted">(eSewa / Khalti / Fonepay integrate fully in Phase 3)</small></h6>

        <div class="border rounded p-3 mb-3">
            <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="cod_enabled" value="1" id="cod" <?= setting('cod_enabled','1')==='1'?'checked':'' ?>><label class="form-check-label fw-bold" for="cod">Cash on Delivery (COD)</label></div>
        </div>

        <div class="border rounded p-3 mb-3">
            <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="esewa_enabled" value="1" id="esewa" <?= setting('esewa_enabled','0')==='1'?'checked':'' ?>><label class="form-check-label fw-bold" for="esewa">eSewa</label></div>
            <div class="row g-2">
                <div class="col-md-5"><div class="form-outline"><input type="text" name="esewa_merchant_id" class="form-control" value="<?= e(setting('esewa_merchant_id','')) ?>"><label class="form-label">Merchant ID</label></div></div>
                <div class="col-md-5"><div class="form-outline"><input type="text" name="esewa_secret" class="form-control" value="<?= e(setting('esewa_secret','')) ?>"><label class="form-label">Secret</label></div></div>
                <div class="col-md-2"><select name="esewa_environment" class="form-select"><option value="test" <?= setting('esewa_environment','test')==='test'?'selected':'' ?>>Test</option><option value="live" <?= setting('esewa_environment','test')==='live'?'selected':'' ?>>Live</option></select><label class="form-label d-block">Env</label></div>
            </div>
        </div>

        <div class="border rounded p-3 mb-3">
            <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="khalti_enabled" value="1" id="khalti" <?= setting('khalti_enabled','0')==='1'?'checked':'' ?>><label class="form-check-label fw-bold" for="khalti">Khalti</label></div>
            <div class="row g-2">
                <div class="col-md-5"><div class="form-outline"><input type="text" name="khalti_public_key" class="form-control" value="<?= e(setting('khalti_public_key','')) ?>"><label class="form-label">Public key</label></div></div>
                <div class="col-md-5"><div class="form-outline"><input type="text" name="khalti_secret_key" class="form-control" value="<?= e(setting('khalti_secret_key','')) ?>"><label class="form-label">Secret key</label></div></div>
                <div class="col-md-2"><select name="khalti_environment" class="form-select"><option value="test" <?= setting('khalti_environment','test')==='test'?'selected':'' ?>>Test</option><option value="live" <?= setting('khalti_environment','test')==='live'?'selected':'' ?>>Live</option></select><label class="form-label d-block">Env</label></div>
            </div>
        </div>

        <div class="border rounded p-3">
            <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="fonepay_enabled" value="1" id="fonepay" <?= setting('fonepay_enabled','0')==='1'?'checked':'' ?>><label class="form-check-label fw-bold" for="fonepay">Fonepay</label></div>
            <div class="row g-2">
                <div class="col-md-6"><div class="form-outline"><input type="text" name="fonepay_merchant_code" class="form-control" value="<?= e(setting('fonepay_merchant_code','')) ?>"><label class="form-label">Merchant code</label></div></div>
                <div class="col-md-6"><div class="form-outline"><input type="text" name="fonepay_secret" class="form-control" value="<?= e(setting('fonepay_secret','')) ?>"><label class="form-label">Secret</label></div></div>
            </div>
        </div>
        <small class="text-muted d-block mt-2">Secrets are stored in the <code>settings</code> table — keep your database &amp; <code>.env</code> secure.</small>
    </div></div>

    <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Settings</button>
</form>
