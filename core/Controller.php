<?php
namespace App\Core;

/**
 * Controller — base class offering view rendering, redirects, JSON output,
 * and a tiny validation helper used by feature controllers.
 */
abstract class Controller
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    /** Render a view in a layout. */
    protected function view(string $template, array $data = [], string $layout = 'public'): void
    {
        (new View($template, $data))->render($layout);
    }

    protected function redirect(string $path = '', int $status = 302): void
    {
        redirect($path, $status);
    }

    protected function back(string $fallback = '/'): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        redirect($referer ?: $fallback);
    }

    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /** Abort with an HTTP status and its error view. */
    protected function abort(int $code = 404): void
    {
        http_response_code($code);
        $view = "errors/{$code}";
        if (is_file(VIEW_PATH . '/' . $view . '.php')) {
            view($view)->render('minimal');
        } else {
            echo "<h1>{$code}</h1>";
        }
        exit;
    }

    /** Flash a success message then redirect. */
    protected function success(string $msg, string $to = ''): void
    {
        Session::setFlash('success', $msg);
        redirect($to);
    }

    /** Flash an error message then redirect. */
    protected function error(string $msg, string $to = ''): void
    {
        Session::setFlash('error', $msg);
        redirect($to);
    }

    /**
     * Re-show the form with validation errors + old input (PRG).
     * Pass the route you want to redirect to and the errors map.
     */
    protected function failWith(string $route, array $errors, ?string $intended = null): void
    {
        Session::setFlash('errors', $errors);
        Session::setFlash('old', $this->request->input());
        if ($intended) {
            Session::setFlash('intended', $intended);
        }
        redirect($route);
    }

    /** Very small validator → returns an associative array of field => message. */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleSet) {
            foreach (explode('|', $ruleSet) as $rule) {
                $value = $data[$field] ?? null;
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
                $msg = $this->applyRule($name, $field, $value, $arg, $data);
                if ($msg && !isset($errors[$field])) {
                    $errors[$field] = $msg;
                }
            }
        }
        return $errors;
    }

    private function applyRule(string $name, string $field, $value, ?string $arg, array $data): ?string
    {
        $label = ucfirst(str_replace('_', ' ', $field));
        switch ($name) {
            case 'required':
                if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
                    return "{$label} is required.";
                }
                break;
            case 'email':
                if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "{$label} must be a valid email address.";
                }
                break;
            case 'numeric':
                if ($value !== '' && !is_numeric($value)) {
                    return "{$label} must be a number.";
                }
                break;
            case 'min':
                if ($value !== '' && strlen((string) $value) < (int) $arg) {
                    return "{$label} must be at least {$arg} characters.";
                }
                break;
            case 'max':
                if ($value !== '' && strlen((string) $value) > (int) $arg) {
                    return "{$label} must be at most {$arg} characters.";
                }
                break;
            case 'in':
                $allowed = explode(',', $arg);
                if ($value !== '' && !in_array($value, $allowed, true)) {
                    return "{$label} is invalid.";
                }
                break;
        }
        return null;
    }
}
