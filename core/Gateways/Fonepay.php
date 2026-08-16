<?php
namespace App\Core\Gateways;

use App\Core\Gateway;

/**
 * Fonepay — redirect gateway with server-to-server verification.
 *
 * Flow: we POST a merchantRequest to the Fonepay client API; Fonepay replies
 * with an XML containing a {url, value} pair which we auto-submit as a form.
 * The customer is redirected back to R1 (success) / R2 (failure) with
 * transaction fields (PRN, PID, BID, AMT, CRN, SUCCESS, TID, DV). We then
 * re-verify server-to-server against the Fonepay merchant verification API.
 *
 * Settings used: fonepay_enabled, fonepay_merchant_code, fonepay_secret,
 * fonepay_environment (test | live).
 */
class Fonepay extends Gateway
{
    public function code(): string
    {
        return 'fonepay';
    }

    public function label(): string
    {
        return 'Fonepay';
    }

    public function isEnabled(): bool
    {
        return $this->cfg('fonepay_enabled', '0') === '1'
            && (string) $this->cfg('fonepay_merchant_code', '') !== ''
            && (string) $this->cfg('fonepay_secret', '') !== '';
    }

    private function apiBase(): string
    {
        return $this->cfg('fonepay_environment', 'test') === 'live'
            ? 'https://clientapi.fonepay.com'
            : 'https://dev-clientapi.fonepay.com';
    }

    public function initiate(array $order): array
    {
        $pid    = (string) $this->cfg('fonepay_merchant_code', '');
        $amt    = number_format((float) $order['total'], 2, '.', '');
        $crn    = 'NPR';
        $prn    = $order['order_number'];
        $secret = (string) $this->cfg('fonepay_secret', '');
        $dv     = hash_hmac('sha256', "$secret,$pid,$prn,$amt,$crn", false);

        $resp = $this->httpPost($this->apiBase() . '/api/merchantRequest', [
            'PID' => $pid,
            'AMT' => $amt,
            'CRN' => $crn,
            'R1'  => url('/payment/return/fonepay'),
            'R2'  => url('/payment/return/fonepay'),
            'PRN' => $prn,
            'DV'  => $dv,
        ]);

        $url   = $this->xmlTag($resp, 'url');
        $value = $this->xmlTag($resp, 'value');
        if ($url === '' || $value === '') {
            return ['method' => 'form', 'url' => '', 'fields' => [], 'error' => 'Could not reach Fonepay. Please try again or choose another method.'];
        }

        return [
            'method' => 'form',
            'url'    => $url,
            'fields' => ['value' => $value],
        ];
    }

    public function verify(array $order, array $request): array
    {
        $pid    = (string) ($request['PID'] ?? $this->cfg('fonepay_merchant_code', ''));
        $bid    = (string) ($request['BID'] ?? '');
        $amt    = $request['AMT'] ?? $order['total'];
        $crn    = (string) ($request['CRN'] ?? 'NPR');
        $prn    = (string) ($request['PRN'] ?? $order['order_number']);
        $secret = (string) $this->cfg('fonepay_secret', '');

        // Verification signature uses the same secret over PID,PRN,BID,AMT,CRN.
        $dv = hash_hmac('sha256', "$secret,$pid,$prn,$bid,$amt,$crn", false);

        $resp = $this->httpPost($this->apiBase() . '/api/merchantRequest/verification/merchant', [
            'PID' => $pid,
            'PRN' => $prn,
            'BID' => $bid,
            'AMT' => $amt,
            'CRN' => $crn,
            'DV'  => $dv,
        ]);

        $success = (int) $this->xmlTag($resp, 'SUCCESS') === 1
            || str_contains($resp, 'SUCCESS:1')
            || str_contains($resp, '"SUCCESS":1');
        $tid = $this->xmlTag($resp, 'TID');

        return [
            'success'         => $success,
            'transaction_ref' => $tid !== '' ? $tid : (string) ($request['TID'] ?? ''),
            'raw'             => $resp,
        ];
    }
}
