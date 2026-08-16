<?php
namespace App\Core;

/**
 * Router — minimal regex router. Supports {param} placeholders, the common
 * verbs, and dispatches to "Controller@action" or a closure.
 */
class Router
{
    private static array $routes = [];

    public static function add(string $method, string $path, $action): void
    {
        $path = '/' . trim($path, '/');
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);
        self::$routes[] = [
            'method'  => strtoupper($method),
            'path'    => $path,
            'pattern' => '#^' . $pattern . '$#',
            'action'  => $action,
        ];
    }

    public static function get(string $p, $a): void    { self::add('GET', $p, $a); }
    public static function post(string $p, $a): void  { self::add('POST', $p, $a); }
    public static function put(string $p, $a): void   { self::add('PUT', $p, $a); }
    public static function delete(string $p, $a): void{ self::add('DELETE', $p, $a); }

    public static function match(array $methods, string $p, $a): void
    {
        foreach ($methods as $m) {
            self::add($m, $p, $a);
        }
    }

    public static function dispatch(Request $request): void
    {
        Csrf::verify();

        $path   = $request->path === '' ? '/' : $request->path;
        $method = $request->method;

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['pattern'], $path, $m)) {
                $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
                self::invoke($route['action'], array_values($params));
                return;
            }
        }

        http_response_code(404);
        view('errors/404')->render('minimal');
    }

    private static function invoke($action, array $params): void
    {
        if (is_callable($action) && !is_array($action)) {
            $action(...$params);
            return;
        }
        if (is_array($action)) {
            [$class, $method] = $action;
            if (!class_exists($class)) {
                throw new \RuntimeException("Controller not found: {$class}");
            }
            $controller = new $class();
            if (!method_exists($controller, $method)) {
                throw new \RuntimeException("Method not found: {$class}::{$method}");
            }
            $controller->{$method}(...$params);
            return;
        }
        throw new \RuntimeException('Invalid route action.');
    }
}
