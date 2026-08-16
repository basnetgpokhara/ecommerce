<?php
/**
 * public/index.php — the single front controller / entry point.
 *
 * Serve in production with Apache + mod_rewrite (see .htaccess files).
 * For local development, PHP's built-in server works too:
 *
 *     php -S 0.0.0.0:8000 -t public public/index.php
 */

// --- When using `php -S`, let existing static files be served natively ---
if (PHP_SAPI === 'cli-server') {
    $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    if ($reqPath !== '/' && $reqPath !== '/index.php') {
        $file = __DIR__ . $reqPath;
        if (is_file($file)) {
            return false; // serve the static asset directly
        }
    }
}

// --- Bootstrap ---
require __DIR__ . '/../config/config.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

Session::start();

// Load routes
require ROOT . '/routes.php';

$dispatched = false;
try {
    $request = new Request();
    Router::dispatch($request);
    $dispatched = true;
} catch (\Throwable $e) {
    http_response_code(500);
    if (APP_DEBUG) {
        echo '<pre style="padding:16px;font-family:monospace">'
           . e($e->getMessage() . "\n\n" . $e->getFile() . ':' . $e->getLine() . "\n\n" . $e->getTraceAsString())
           . '</pre>';
    } else {
        view('errors/500')->render('minimal');
    }
} finally {
    // Flash data lives for exactly one request after it is set (PRG pattern).
    Session::clearFlash();
}
