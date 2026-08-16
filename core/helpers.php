<?php
/**
 * core/helpers.php — global helper functions (global namespace).
 * Kept minimal & explicit; the framework classes live in App\Core\*.
 */

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Models\Setting;

/** HTML-escape a value for output (XSS prevention). */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build an absolute URL rooted at APP_URL. */
function url(string $path = ''): string
{
    if ($path === '') {
        return APP_URL;
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return APP_URL . '/' . ltrim($path, '/');
}

/** URL to an asset under /public/assets. */
function asset(string $path): string
{
    return APP_URL . '/assets/' . ltrim($path, '/');
}

/** Resolve a stored image path (stored relative to /public, e.g. uploads/x.jpg or assets/img/y.svg). */
function media_url(?string $path): string
{
    if ($path === null || $path === '') {
        return asset('img/placeholder.svg');
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return APP_URL . '/' . ltrim($path, '/');
}

/** Redirect (absolute or relative path) and stop. */
function redirect(string $path = '', int $status = 302): void
{
    $target = (str_starts_with($path, 'http')) ? $path : url($path);
    header("Location: {$target}", true, $status);
    exit;
}

/** Render a view within a layout. */
function view(string $template, array $data = []): View
{
    return new View($template, $data);
}

/** Auth facade. */
function auth(): Auth
{
    return Auth::instance();
}

function current_user(): ?array
{
    return Auth::user();
}

/** Format a money amount using the site currency symbol (default NPR). */
function money($amount): string
{
    $symbol = setting('currency_symbol', 'रू');
    return $symbol . ' ' . number_format((float) $amount, 2);
}

/** Read a site setting (settings table) with a default. */
function setting(string $key, $default = null)
{
    return Setting::get($key, $default);
}

/** Old form input (from last validation flash). */
function old(string $key, $default = '')
{
    $old = Session::getFlash('old', []);
    return $old[$key] ?? $default;
}

/** Validation errors: all, or for a single field. */
function errors(?string $field = null)
{
    $errors = Session::getFlash('errors', []);
    if ($field === null) {
        return $errors;
    }
    return $errors[$field] ?? null;
}

function has_error(string $field): bool
{
    return !empty(errors($field));
}

/** A single flash message of a given type (success|error|info|warning). */
function flash(string $type = 'success')
{
    return Session::getFlash($type);
}

/** Current CSRF token. */
function csrf_token(): string
{
    return Csrf::token();
}

/** Hidden input carrying the CSRF token for forms. */
function csrf_field(): string
{
    return '<input type="hidden" name="' . e(CSRF_TOKEN_NAME) . '" value="' . e(csrf_token()) . '">';
}

/** Spoof an HTTP verb (PUT/DELETE) in a POST form. */
function method_field(string $verb): string
{
    return '<input type="hidden" name="_method" value="' . e(strtoupper($verb)) . '">';
}

/** Render a Bootstrap/MDB alert if there is a flash message of that type. */
function render_flash(): string
{
    $types = ['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'];
    $out = '';
    foreach ($types as $key => $color) {
        if ($msg = Session::getFlash($key)) {
            $out .= '<div class="alert alert-' . $color . ' alert-dismissible fade show" role="alert">'
                  . e($msg)
                  . '<button type="button" class="btn-close" data-mdb-dismiss="alert"></button></div>';
        }
    }
    return $out;
}

/** Build a query-string URL preserving existing params and overriding some (for pagination/filters). */
function query_url(string $path, array $override = []): string
{
    $params = array_merge($_GET, $override);
    $params = array_filter($params, fn($v) => $v !== null && $v !== '');
    $qs = http_build_query($params);
    return url($path) . ($qs !== '' ? '?' . $qs : '');
}

/** Sanitize a filename for safe storage. */
function safe_filename(string $name): string
{
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?? 'file';
    return trim($name, '._');
}
