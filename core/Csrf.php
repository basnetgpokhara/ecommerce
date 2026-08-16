<?php
namespace App\Core;

/**
 * CSRF protection — one token per session, verified on every state-changing
 * request (POST/PUT/PATCH/DELETE).
 */
class Csrf
{
    /** Return the current session token, creating one if needed. */
    public static function token(): string
    {
        $token = Session::get('csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return $token;
    }

    /** Verify the token on non-safe methods; abort with 419 on mismatch. */
    public static function verify(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        $sent = $_POST[CSRF_TOKEN_NAME]
              ?? $_SERVER['HTTP_X_CSRF_TOKEN']
              ?? $_SERVER['HTTP_X_XSRF_TOKEN']
              ?? null;

        $expected = Session::get('csrf_token');

        if (!$sent || !$expected || !hash_equals($expected, (string) $sent)) {
            http_response_code(419);
            view('errors/419')->render('minimal');
            exit;
        }
    }
}
