<?php /** @var array $order @var array $items — A4 print-friendly invoice (minimal layout). */ ?>
<div class="invoice-toolbar no-print">
    <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-1"></i> Print / Save as PDF</button>
    <a class="btn btn-outline-secondary" href="<?= url('/account/orders/' . $order['id']) ?>">Back to order</a>
</div>

<div class="invoice">
    <div class="invoice-head">
        <div>
            <img src="<?= asset('img/logo.svg') ?>" alt="logo" height="40">
            <h2 class="invoice-brand"><?= e(setting('site_name', APP_NAME)) ?></h2>
            <p class="invoice-meta">
                <?= e(setting('contact_email', 'hello@nepmart.test')) ?> · <?= e(setting('contact_phone', '+977 1-000-0000')) ?>
            </p>
        </div>
        <div class="text-end">
            <h3 class="invoice-title">TAX INVOICE</h3>
            <p class="invoice-meta"><strong>No.:</strong> <?= e($order['order_number']) ?></p>
            <p class="invoice-meta"><strong>Date:</strong> <?= e(date('M j, Y', strtotime($order['created_at']))) ?></p>
            <p class="invoice-meta">
                <strong>Status:</strong>
                <span class="invoice-status <?= e($order['payment_status']) ?>"><?= ucfirst(e($order['payment_status'])) ?></span>
            </p>
        </div>
    </div>

    <div class="invoice-parties">
        <div>
            <h6 class="text-uppercase text-muted">Bill To</h6>
            <strong><?= e($order['shipping_name']) ?></strong><br>
            <?= e($order['shipping_phone']) ?>
        </div>
        <div>
            <h6 class="text-uppercase text-muted">Ship To</h6>
            <?= nl2br(e($order['shipping_address'])) ?><br>
            <?= e($order['shipping_city']) ?><?= !empty($order['shipping_district']) ? ', ' . e($order['shipping_district']) : '' ?>
        </div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>#</th>
                <th class="text-start">Item</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Unit Price</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $i => $it): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td class="text-start"><?= e($it['product_name']) ?></td>
                <td class="text-end"><?= (int)$it['quantity'] ?></td>
                <td class="text-end"><?= money($it['unit_price']) ?></td>
                <td class="text-end"><?= money($it['subtotal']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="invoice-totals">
        <table>
            <tr><td>Subtotal</td><td><?= money($order['subtotal']) ?></td></tr>
            <tr><td>Shipping</td><td><?= $order['shipping_fee'] == 0 ? 'Free' : money($order['shipping_fee']) ?></td></tr>
            <?php if ((float)$order['discount'] > 0): ?>
            <tr><td>Discount</td><td>- <?= money($order['discount']) ?></td></tr>
            <?php endif; ?>
            <?php if ((float)$order['tax'] > 0): ?>
            <tr><td>Tax</td><td><?= money($order['tax']) ?></td></tr>
            <?php endif; ?>
            <tr class="grand-total"><td>Total</td><td><?= money($order['total']) ?></td></tr>
            <tr><td colspan="2" class="text-end small text-muted pt-2">Payment method: <?= e(strtoupper($order['payment_method'])) ?> (<?= ucfirst(e($order['payment_status'])) ?>)</td></tr>
        </table>
    </div>

    <div class="invoice-foot">
        <p>Thank you for shopping with <?= e(setting('site_name', APP_NAME)) ?>!</p>
        <p class="small text-muted">This is a computer-generated invoice and does not require a signature.</p>
    </div>
</div>
