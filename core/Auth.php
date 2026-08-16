<?php
namespace App\Core;

use App\Models\User;

/**
 * Auth — session-based authentication + RBAC guards.
 * The logged-in user is loaded once per request from the users table.
 */
class Auth
{
    private static ?Auth $instance = null;
    private ?array $user = null;

    private function __construct()
    {
        $this->loadUser();
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function loadUser(): void
    {
        $id = Session::get('user_id');
        if (!$id) {
            return;
        }
        $user = User::find((int) $id);
        if (!$user || ($user['status'] ?? '') === 'blocked') {
            Session::forget('user_id');
            Session::forget('role');
            return;
        }
        $this->user = $user;
    }

    public static function user(): ?array
    {
        return self::instance()->user;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    public static function is(string ...$roles): bool
    {
        $u = self::user();
        return $u !== null && in_array($u['role'], $roles, true);
    }

    public static function login(array $user): void
    {
        Session::regenerate();               // prevent session fixation
        Session::set('user_id', (int) $user['id']);
        Session::set('role', $user['role']);
        self::instance()->user = $user;
    }

    public static function logout(): void
    {
        Session::forget('user_id');
        Session::forget('role');
        self::instance()->user = null;
        Session::regenerate();
    }

    /** Redirect guests to login (remembering the intended URL). */
    public static function requireAuth(string $redirect = '/login'): void
    {
        if (!self::check()) {
            Session::setFlash('intended', $_SERVER['REQUEST_URI'] ?? '/');
            Session::setFlash('error', 'Please log in to continue.');
            redirect($redirect);
        }
    }

    /** Require an authenticated user with one of the given roles. */
    public static function requireRole(string ...$roles): void
    {
        self::requireAuth();
        if (!self::is(...$roles)) {
            http_response_code(403);
            view('errors/403')->render('minimal');
            exit;
        }
    }

    /** Where to send the user after login. */
    public static function redirectAfterLogin(): string
    {
        $intended = Session::getFlash('intended');
        if ($intended) {
            return $intended;
        }
        return match (self::role()) {
            'admin'  => '/admin',
            'seller' => '/seller',
            default  => '/account',
        };
    }
}
