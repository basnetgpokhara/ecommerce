<?php
namespace App\Core;

use App\Models\Setting;

/**
 * Gateway — abstract base for online payment gateways.
 *
 * Subclasses implement two steps:
 *   1. initiate($order) — build the payload used to redirect the customer to
 *      the gateway (a POST form for eSewa/Fonepay, a JS widget config for
 *      Khalti).
 *   2. verify($order, $request) — perform the server-to-server verification
 *      call with the gateway using the callback/return parameters, returning
 *      an associative result: ['success' => bool, 'transaction_ref' => string,
 *      'raw' => string].
 *
 * Credentials are read live from the `settings` table (Admin → Settings),
 * so nothing is hard-coded and no code changes are needed to go live.
 */
abstract class Gateway
{
    /** Unique code matching the orders.payment_method ENUM. */
    abstract public function code(): string;

    /** Human-readable gateway name. */
    abstract public function label(): string;

    /** Whether the gateway is enabled AND configured with credentials. */
    abstract public function isEnabled(): bool;

    /** Build the initiation payload for an order. */
    abstract public function initiate(array $order): array;

    /** Verify a transaction server-to-server using the callback request. */
    abstract public function verify(array $order, array $request): array;

    /** Read a config value from the settings table. */
    protected function cfg(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }

    // ------------------------------------------------------------------
    //  cURL helpers (no Composer dependency — native PHP ext/curl)
    // ------------------------------------------------------------------
    protected function httpGet(string $url, array $params = []): string
    {
        if ($params) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($params);
        }
        return $this->send($url, 'GET');
    }

    protected function httpPost(string $url, array $data = []): string
    {
        return $this->send($url, 'POST', $data);
    }

    protected function httpPostJson(string $url, array $data = [], array $headers = []): string
    {
        return $this->send($url, 'POST', $data, array_merge($headers, ['Content-Type: application/json']), true);
    }

    private function send(string $url, string $method, array $data = [], array $headers = [], bool $json = false): string
    {
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
        ];
        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $json ? json_encode($data) : http_build_query($data);
        }
        if ($headers) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, $opts);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($err !== '') {
            return '';
        }
        return $body === false ? '' : (string) $body;
    }

    /** Extract the value of an XML tag (used by eSewa / Fonepay responses). */
    protected function xmlTag(string $xml, string $tag): string
    {
        if ($xml === '') {
            return '';
        }
        if (preg_match('/<' . preg_quote($tag, '/') . '>(.*?)<\/' . preg_quote($tag, '/') . '>/is', $xml, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    // ------------------------------------------------------------------
    //  Factory
    // ------------------------------------------------------------------
    /** Instantiate a gateway by code, or null if unknown. */
    public static function make(string $code): ?self
    {
        $map = [
            'esewa'   => Gateways\Esewa::class,
            'khalti'  => Gateways\Khalti::class,
            'fonepay' => Gateways\Fonepay::class,
        ];
        $class = $map[$code] ?? null;
        if (!$class || !class_exists($class)) {
            return null;
        }
        return new $class();
    }

    /** All configured & enabled gateways, keyed by code. */
    public static function enabled(): array
    {
        $out = [];
        foreach (['esewa', 'khalti', 'fonepay'] as $code) {
            $g = self::make($code);
            if ($g && $g->isEnabled()) {
                $out[$code] = $g;
            }
        }
        return $out;
    }
}
