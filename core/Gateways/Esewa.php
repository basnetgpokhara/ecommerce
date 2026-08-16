<?php
namespace App\Core\Gateways;

use App\Core\Gateway;

/**
 * eSewa — redirect gateway with server-to-server verification.
 *
 * Flow: we POST an auto-submitting form to the eSewa "epay/main" endpoint.
 * eSewa redirects the customer back to our return URL (su / fu) carrying a
 * reference id. We then verify the transaction server-to-server via the
 * eSewa "epay/transrec" endpoint.
 *
 * Settings used: esewa_enabled, esewa_merchant_id (product code), esewa_secret,
 * esewa_environment (test | live).
 */
class Esewa extends Gateway
{
    public function code(): string
    {
        return 'esewa';
    }

    public function label(): string
    {
        return 'eSewa';
    }

    public function isEnabled(): bool
    {
        return $this->cfg('esewa_enabled', '0') === '1'
            && (string) $this->cfg('esewa_merchant_id', '') !== ''
            && (string) $this->cfg('esewa_secret', '') !== '';
    }

    private function baseUrl(): string
    {
        return $this->cfg('esewa_environment', 'test') === 'live'
            ? 'https://esewa.com.np'
            : 'https://uat.esewa.com.np';
    }

    public function initiate(array $order): array
    {
        $total = number_format((float) $order['total'], 2, '.', '');
        $scd   = (string) $this->cfg('esewa_merchant_id', '');
        $uuid  = $order['order_number'] . '-T' . strtoupper(bin2hex(random_bytes(6)));
        // eSewa signature: base64(HMAC-SHA256("<total_amount>,<transaction_uuid>,<product_code>"))
        $sig = base64_encode(hash_hmac(
            'sha256',
            $total . ',' . $uuid . ',' . $scd,
            (string) $this->cfg('esewa_secret', ''),
            true
        ));

        return [
            'method'  => 'form',
            'url'     => $this->baseUrl() . '/epay/main',
            'fields'  => [
                'amt'   => $total,
                'pdc'   => '0',
                'psc'   => '0',
                'txAmt' => '0',
                'tAmt'  => $total,
                'pid'   => $order['order_number'],
                'scd'   => $scd,
                'su'    => url('/payment/return/esewa'),
                'fu'    => url('/payment/return/esewa'),
                'sct'   => $sig,
            ],
        ];
    }

    public function verify(array $order, array $request): array
    {
        $ref  = (string) ($request['refId'] ?? $request['reference_id'] ?? '');
        $amt  = $request['amt'] ?? $request['tAmt'] ?? $order['total'];

        $resp = $this->httpGet($this->baseUrl() . '/epay/transrec', [
            'amt' => (float) $amt,
            'rid' => $ref,
            'pid' => $order['order_number'],
            'scd' => (string) $this->cfg('esewa_merchant_id', ''),
        ]);

        // Response is XML: <response><response_code>Success</response_code>...
        $ok = $this->xmlTag($resp, 'response_code') === 'Success'
            || str_contains($resp, 'Success');

        return [
            'success'         => $ok,
            'transaction_ref' => $ref,
            'raw'             => $resp,
        ];
    }
}
