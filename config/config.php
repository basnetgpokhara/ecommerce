<?php
/**
 * config/config.php
 * ------------------------------------------------------------------
 * Application bootstrap: paths, .env loading, constants, autoloader.
 * This file must not produce output. Required by /public/index.php.
 */

// ----------------------------------------------------------------------
// 1. Paths
// ----------------------------------------------------------------------
define('ROOT',        dirname(__DIR__));                 // .../ecommerce
define('APP',         ROOT . '/app');
define('CORE',        ROOT . '/core');
define('CONFIG_DIR',  ROOT . '/config');
define('PUBLIC_PATH', ROOT . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('VIEW_PATH',   APP . '/views');

// ----------------------------------------------------------------------
// 2. Minimal .env parser (no external dependencies)
// ----------------------------------------------------------------------
$_ENV_LOADED = [];
$envFile = ROOT . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        // Strip a trailing inline comment that is outside quotes
        if (!preg_match('/^["\']/', $v) && strpos($v, '#') !== false) {
            $v = trim(substr($v, 0, strpos($v, '#')));
        }
        // Unquote
        if (strlen($v) >= 2 && (
            ($v[0] === '"' && substr($v, -1) === '"') ||
            ($v[0] === "'" && substr($v, -1) === "'")
        )) {
            $v = substr($v, 1, -1);
        }
        $_ENV_LOADED[$k] = $v;
        putenv("$k=$v");
    }
}

function env(string $key, $default = null)
{
    global $_ENV_LOADED;
    if (array_key_exists($key, $_ENV_LOADED)) {
        $v = $_ENV_LOADED[$key];
    } else {
        $v = getenv($key);
    }
    return ($v === false || $v === null || $v === '') ? $default : $v;
}

// ----------------------------------------------------------------------
// 3. Application constants
// ----------------------------------------------------------------------
define('APP_NAME',          env('APP_NAME', 'NepMart Marketplace'));
define('APP_ENV',           env('APP_ENV', 'production'));
define('APP_DEBUG',         env('APP_DEBUG', 'false') === 'true');
// ---- APP_URL / BASE : auto-detected from the request ----
// The app figures out its own sub-folder (e.g. http://localhost/nepmart)
// from the front controller's script path, so you DON'T need to set APP_URL
// for local/XAMPP installs. Set APP_URL in .env only to override (production,
// reverse proxy, etc.). APP_URL='http://localhost:8000' is treated as "unset".
$envAppUrl = rtrim((string) env('APP_URL', ''), '/');

if (PHP_SAPI === 'cli') {
    // CLI context (seeder, etc.) — no request; fall back to .env or localhost.
    define('BASE', rtrim((string) (parse_url($envAppUrl ?: 'http://localhost', PHP_URL_PATH) ?? ''), '/'));
    define('APP_URL', $envAppUrl !== '' ? $envAppUrl : 'http://localhost');
} else {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $computedBase = '';
    if (str_ends_with($scriptName, '/public/index.php')) {
        $computedBase = substr($scriptName, 0, -strlen('/public/index.php'));
    } elseif (str_ends_with($scriptName, '/index.php')) {
        $computedBase = substr($scriptName, 0, -strlen('/index.php'));
    } elseif (str_ends_with($scriptName, 'index.php')) {
        $computedBase = substr($scriptName, 0, -strlen('index.php'));
    }
    define('BASE', rtrim($computedBase, '/'));

    if ($envAppUrl !== '' && $envAppUrl !== 'http://localhost:8000') {
        define('APP_URL', $envAppUrl); // explicit override
    } else {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        define('APP_URL', rtrim($scheme . '://' . $host . BASE, '/'));
    }
}

define('DB_HOST',           env('DB_HOST', '127.0.0.1'));
define('DB_PORT',           env('DB_PORT', '3306'));
define('DB_NAME',           env('DB_NAME', 'ecommerce'));
define('DB_USER',           env('DB_USER', 'root'));
define('DB_PASS',           env('DB_PASS', ''));
define('DB_CHARSET',        'utf8mb4');

define('SESSION_LIFETIME',  (int) env('SESSION_LIFETIME', 7200));
define('SESSION_NAME',      env('SESSION_NAME', 'nepmart_session'));
define('CSRF_TOKEN_NAME',   env('CSRF_TOKEN_NAME', '_csrf'));

// ----------------------------------------------------------------------
// 4. PSR-4 style autoloader (App\Core\ / App\Controllers\ / App\Models\)
// ----------------------------------------------------------------------
spl_autoload_register(function (string $class): void {
    $map = [
        'App\\Core\\'        => CORE . '/',
        'App\\Controllers\\' => APP . '/controllers/',
        'App\\Models\\'      => APP . '/models/',
    ];
    foreach ($map as $prefix => $base) {
        if (str_starts_with($class, $prefix)) {
            $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
            $file = $base . $relative . '.php';
            if (is_file($file)) {
                require $file;
                return;
            }
        }
    }
});

// ----------------------------------------------------------------------
// 5. Global helper functions
// ----------------------------------------------------------------------
require CORE . '/helpers.php';
