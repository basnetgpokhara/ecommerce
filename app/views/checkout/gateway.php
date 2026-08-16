<?php /** @var array $order @var \App\Core\Gateway $gateway @var array $payload */ ?>
<?php $title = 'Processing Payment'; ?>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
<div style="max-width:460px;margin:10vh auto;background:#fff;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,.08);padding:36px 32px;text-align:center;">
    <?php if (($payload['method'] ?? '') === 'khalti'): ?>
        <div class="spinner mx-auto" style="width:44px;height:44px;border:4px solid #e2e8f0;border-top-color:#3b71ca;border-radius:50%;margin:0 auto 18px;animation:spin 1s linear infinite;"></div>
        <h1 class="h5 fw-bold mb-1">Opening <?= e($gateway->label()) ?> Checkout</h1>
        <p class="text-muted mb-0">Please complete the payment in the Khalti pop-up window.</p>
        <div id="khalti-widget"></div>
    <?php elseif (!empty($payload['url'])): ?>
        <div class="spinner mx-auto" style="width:44px;height:44px;border:4px solid #e2e8f0;border-top-color:#3b71ca;border-radius:50%;margin:0 auto 18px;animation:spin 1s linear infinite;"></div>
        <h1 class="h5 fw-bold mb-1">Redirecting to <?= e($gateway->label()) ?>…</h1>
        <p class="text-muted mb-0">Please wait — you will be taken to the secure payment page.</p>
    <?php else: ?>
        <i class="fas fa-triangle-exclamation fa-2x text-danger mb-3"></i>
        <h1 class="h5 fw-bold mb-1">Payment unavailable</h1>
        <p class="text-muted"><?= e($payload['error'] ?? 'The payment gateway could not be reached.') ?></p>
        <a href="<?= url('/checkout') ?>" class="btn btn-outline-primary mt-2">Back to checkout</a>
    <?php endif; ?>
    <div class="text-muted small mt-4">Order <?= e($order['order_number']) ?> · <?= money($order['total']) ?></div>
</div>

<form method="post" action="<?= e($payload['url'] ?? '') ?>" id="gatewayForm">
    <?php if (($payload['method'] ?? '') === 'form' && !empty($payload['url'])): ?>
        <?php foreach ($payload['fields'] as $k => $v): ?>
            <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</form>

<?php if (($payload['method'] ?? '') === 'khalti'): ?>
    <script src="<?= e($payload['checkout_js_url'] ?? 'https://testkhalti.com/khalti-checkout.js') ?>"></script>
    <script>
        var config = {
            publicKey: "<?= e($payload['public_key'] ?? '') ?>",
            productIdentity: "<?= e($payload['purchase_order_id'] ?? '') ?>",
            productName: "<?= e($payload['purchase_order_name'] ?? 'NepMart Order') ?>",
            productUrl: "<?= e(url('/')) ?>",
            eventHandler: {
                onSuccess: function (payload) {
                    fetch("<?= e($payload['verify_url'] ?? '') ?>", {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            token: payload.token,
                            amount: payload.amount,
                            purchase_order_id: "<?= e($payload['purchase_order_id'] ?? '') ?>"
                        })
                    }).then(function (r) { return r.json(); }).then(function (res) {
                        if (res && res.success) {
                            window.location = res.redirect_url;
                        } else {
                            alert(res.message || 'Payment could not be verified.');
                            window.location = "<?= e(url('/checkout/failure/' . $order['id'])) ?>";
                        }
                    }).catch(function () {
                        window.location = "<?= e(url('/checkout/failure/' . $order['id'])) ?>";
                    });
                },
                onError: function (error) {
                    alert(error);
                    window.location = "<?= e(url('/checkout/failure/' . $order['id'])) ?>";
                },
                onClose: function () {
                    window.location = "<?= e(url('/checkout/failure/' . $order['id'])) ?>";
                }
            }
        };
        var checkout = new KhaltiCheckout(config);
        checkout.show({amount: <?= (int) ($payload['amount_paisa'] ?? 0) ?>});
    </script>
<?php elseif (($payload['method'] ?? '') === 'form' && !empty($payload['url'])): ?>
    <script>document.getElementById('gatewayForm').submit();</script>
<?php endif; ?>
