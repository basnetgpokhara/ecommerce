<?php
namespace App\Core;

/**
 * Request — normalizes method (supports _method spoofing), strips the
 * configured base path, and exposes query/body/file input.
 */
class Request
{
    public string $method;
    public string $uri;
    public string $path;
    public array $query;
    public array $body;
    public array $files;

    public function __construct()
    {
        $this->method = $this->resolveMethod();
        $this->uri    = $_SERVER['REQUEST_URI'] ?? '/';
        $rawPath      = parse_url($this->uri, PHP_URL_PATH) ?? '/';
        $this->path   = $this->stripBase($rawPath);
        $this->query  = $_GET;
        $this->body   = $_POST;
        $this->files  = $_FILES;
    }

    private function resolveMethod(): string
    {
        $m = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($m === 'POST' && isset($_POST['_method'])) {
            $m = strtoupper($_POST['_method']);
        }
        return $m;
    }

    private function stripBase(string $path): string
    {
        if (BASE !== '' && str_starts_with($path, BASE)) {
            $path = substr($path, strlen(BASE));
        }
        return '/' . ltrim($path, '/');
    }

    /** Get a body value, falling back to query, then a default. */
    public function input(?string $key = null, $default = null)
    {
        if ($key === null) {
            return array_merge($this->query, $this->body);
        }
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function only(array $keys): array
    {
        $all = array_merge($this->query, $this->body);
        $out = [];
        foreach ($keys as $k) {
            if (array_key_exists($k, $all)) {
                $out[$k] = $all[$k];
            }
        }
        return $out;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->body) || array_key_exists($key, $this->query);
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }
}
