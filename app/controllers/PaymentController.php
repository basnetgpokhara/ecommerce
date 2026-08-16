<?php
namespace App\Controllers;

use App\Core\Gateway;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payment;

/**
 * PaymentController — handles online payment gateway returns/callbacks.
 *
 * These endpoints are intentionally CSRF-exempt (see routes.php) because they
 * are invoked by external gateways. Security comes from the server-to-server
 * verification each gateway performs against its own API using the configured
 * secret — never from trusting the incoming request alone.
 */
class PaymentController extends \App\Core\Controller
{
    public function __construct()
    {
        parent::__construct();
        // No auth guard here: the customer may not be logged in when a gateway
        // redirects them back. Order access is still bound by order_number.
    }

    private function markPaid(array $order, string $ref, string $raw): void
    {
        Order::updateById((int) $order['id'], [
            'payment_status' => 'paid',
            'status'         => 'confirmed',
        ]);
        Payment::recordSuccess((int) $order['id'], $ref, $raw);
        AuditLog::record((int) $order['customer_id'], 'payment.success', "Order {$order['order_number']} paid");
    }

    private function markFailed(array $order, string $raw): void
    {
        Order::updateById((int) $order['id'], ['payment_status' => 'failed']);
        Payment::recordFailure((int) $order['id'], $raw);
        AuditLog::record((int) $order['customer_id'], 'payment.failed', "Order {$order['order_number']} payment failed");
    }

    /**
     * Unified return handler for eSewa & Fonepay. The gateway redirects here
     * on both success and cancellation; we always re-verify server-to-server
     * and only trust the verified result.
     */
    public function return(string $gateway): void
    {
        $g = Gateway::make($gateway);
        if (!$g) {
            $this->abort(404);
        }

        $input       = $this->request->input();
        // eSewa returns `oid`/`pid`, Fonepay returns `PRN`, Khalti uses purchase_order_id.
        $orderNumber = (string) ($input['oid'] ?? $input['pid'] ?? $input['PRN'] ?? $input['purchase_order_id'] ?? '');
        $order       = $orderNumber !== '' ? Order::findByNumber($orderNumber) : null;
        if (!$order) {
            $this->abort(404);
        }

        $payment = Payment::latestForOrder((int) $order['id']);
        if ($payment && $payment['status'] === 'success') {
            redirect('/checkout/success/' . $order['id']);
        }

        $result = $g->verify($order, $input);

        if ($result['success']) {
            $this->markPaid($order, (string) $result['transaction_ref'], (string) $result['raw']);
            redirect('/checkout/success/' . $order['id']);
        }

        $this->markFailed($order, (string) $result['raw']);
        redirect('/checkout/failure/' . $order['id']);
    }

    /**
     * Khalti widget callback. Returns JSON consumed by the Khalti Checkout JS.
     */
    public function khaltiVerify(): void
    {
        $gateway = Gateway::make('khalti');
        if (!$gateway) {
            $this->json(['success' => false, 'message' => 'Khalti is not configured.']);
        }

        $orderNumber = (string) $this->request->input('purchase_order_id', '');
        $order       = $orderNumber !== '' ? Order::findByNumber($orderNumber) : null;
        if (!$order) {
            $this->json(['success' => false, 'message' => 'Order not found.']);
        }

        $payment = Payment::latestForOrder((int) $order['id']);
        if ($payment && $payment['status'] === 'success') {
            $this->json(['success' => true, 'redirect_url' => url('/checkout/success/' . $order['id'])]);
        }

        $result = $gateway->verify($order, $this->request->input());

        if ($result['success']) {
            $this->markPaid($order, (string) $result['transaction_ref'], (string) $result['raw']);
            $this->json(['success' => true, 'redirect_url' => url('/checkout/success/' . $order['id'])]);
        }

        $this->markFailed($order, (string) $result['raw']);
        $this->json(['success' => false, 'message' => 'Payment could not be verified. Please contact support.']);
    }
}
