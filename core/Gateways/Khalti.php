<?php
namespace App\Core\Gateways;

use App\Core\Gateway;

/**
 * Khalti — JS widget with server-to-server verification.
 *
 * Flow: the checkout page loads Khalti Checkout JS with our public key and
 * the order amount (in paisa). On success the widget posts {token, amount,
 * purchase_order_id} back to our /payment/khalti/verify endpoint, which we
 * verify server-to-server against the Khalti Verify API using the secret key.
 *
 * Settings used: khalti_enabled, khalti_public_key, khalti_secret_key,
 * khalti_environment (test | live).
 */
class Khalti extends Gateway
{
    public function code(): string
    {
        return 'khalti';
    }

    public function label(): string
    {
        return 'Khalti';
    }

    public function isEnabled(): bool
    {
        return $this->cfg('khalti_enabled', '0') === '1'
            && (string) $this->cfg('khalti_public_key', '') !== ''
            && (string) $this->cfg('khalti_secret_key', '') !== '';
    }

    public function baseUrl(): string
    {
        return $this->cfg('khalti_environment', 'test') === 'live'
            ? 'https://khalti.com'
            : 'https://testkhalti.com';
    }

    /** URL of the Khalti Checkout JS widget (matches the current environment). */
    public function checkoutJsUrl(): string
    {
        return $this->cfg('khalti_environment', 'test') === 'live'
            ? 'https://khalti.com/khalti-checkout.js'
            : 'https://testkhalti.com/khalti-checkout.js';
    }

    public function initiate(array $order): array
    {
        return [
            'method'              => 'khalti',
            'public_key'          => (string) $this->cfg('khalti_public_key', ''),
            'purchase_order_id'   => $order['order_number'],
            'purchase_order_name' => 'NepMart Order ' . $order['order_number'],
            'amount_paisa'        => (int) round((float) $order['total'] * 100),
            'return_url'          => url('/payment/return/khalti'),
            'verify_url'          => url('/payment/khalti/verify'),
            'checkout_js_url'     => $this->checkoutJsUrl(),
        ];
    }

    public function verify(array $order, array $request): array
    {
        $token  = (string) ($request['token'] ?? '');
        $paisa  = (int) ($request['amount'] ?? 0);
        $secret = (string) $this->cfg('khalti_secret_key', '');

        $body = $this->httpPostJson(
            $this->baseUrl() . '/api/v2/payment/verify/',
            ['token' => $token, 'amount' => $paisa],
            ['Authorization: Key ' . $secret]
        );

        $data  = json_decode($body, true);
        $state = $data['state']['name'] ?? null;
        $ok    = $state === 'Completed';

        return [
            'success'         => $ok,
            'transaction_ref' => (string) ($data['idx'] ?? ($token ?: '')),
            'raw'             => $body,
        ];
    }
}
