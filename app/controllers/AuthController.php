<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Seller;
use App\Models\User;

class AuthController extends \App\Core\Controller
{
    private const MAX_ATTEMPTS   = 5;
    private const LOCKOUT_WINDOW = 900; // 15 minutes (seconds)

    // ------------------------------------------------------------------
    //  Login
    // ------------------------------------------------------------------
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect(Auth::redirectAfterLogin());
        }
        $this->view('auth/login', ['title' => 'Login'], 'auth');
    }

    public function login(): void
    {
        if ($this->isLockedOut()) {
            $this->error('Too many failed attempts. Please try again in a few minutes.', '/login');
        }

        $data = $this->request->only(['email', 'password']);
        $errors = $this->validate($data, [
            'email'    => 'required|email',
            'password' => 'required',
        ]);
        if ($errors) {
            $this->failWith('/login', $errors);
        }

        $user = User::findByEmail($data['email']);
        if (!$user
            || ($user['status'] === 'blocked')
            || !password_verify($data['password'], $user['password_hash'])
        ) {
            $this->registerFailedAttempt();
            $this->failWith('/login', ['email' => 'Invalid credentials or account blocked.']);
        }

        $this->clearAttempts();
        Auth::login($user);
        AuditLog::record((int) $user['id'], 'auth.login', 'User logged in');
        redirect(Auth::redirectAfterLogin());
    }

    // ------------------------------------------------------------------
    //  Registration (customer or seller)
    // ------------------------------------------------------------------
    public function showRegister(): void
    {
        if (Auth::check()) {
            redirect(Auth::redirectAfterLogin());
        }
        $role = $this->request->query('role', 'customer') === 'seller' ? 'seller' : 'customer';
        $this->view('auth/register', ['title' => 'Create Account', 'role' => $role], 'auth');
    }

    public function register(): void
    {
        $role = $this->request->input('role', 'customer') === 'seller' ? 'seller' : 'customer';
        $data = $this->request->only(['name', 'email', 'phone', 'password', 'password_confirm']);
        if ($role === 'seller') {
            $data['shop_name'] = $this->request->input('shop_name', '');
        }

        $rules = [
            'name'             => 'required|min:2|max:120',
            'email'            => 'required|email',
            'phone'            => 'required|min:7|max:30',
            'password'         => 'required|min:8|max:72',
            'password_confirm' => 'required',
        ];
        if ($role === 'seller') {
            $rules['shop_name'] = 'required|min:2|max:150';
        }
        $errors = $this->validate($data, $rules);

        if ($data['password'] !== $data['password_confirm']) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }
        if (!preg_match('/[A-Za-z]/', $data['password']) || !preg_match('/[0-9]/', $data['password'])) {
            $errors['password'] = $errors['password'] ?? 'Password must include letters and numbers.';
        }
        if (User::findByEmail($data['email'])) {
            $errors['email'] = 'An account with this email already exists.';
        }

        if ($errors) {
            $this->failWith('/register' . ($role === 'seller' ? '?role=seller' : ''), $errors);
        }

        $userId = User::create([
            'name'           => trim($data['name']),
            'email'          => strtolower(trim($data['email'])),
            'phone'          => trim($data['phone']),
            'password_hash'  => password_hash($data['password'], PASSWORD_DEFAULT),
            'role'           => $role,
            'status'         => 'active',
        ]);

        if ($role === 'seller') {
            $this->createSeller($userId, $data['shop_name']);
        }

        Auth::login(User::find($userId));
        AuditLog::record($userId, 'auth.register', "New {$role} registered");

        if ($role === 'seller') {
            Session::setFlash('success', 'Welcome! Your shop is pending admin approval — you can explore the seller dashboard now.');
            redirect('/seller');
        }
        Session::setFlash('success', 'Welcome to ' . e(setting('site_name', APP_NAME)) . '! Your account is ready.');
        redirect('/account');
    }

    private function createSeller(int $userId, string $shopName): int
    {
        return Seller::create([
            'user_id'         => $userId,
            'shop_name'       => $shopName,
            'slug'            => $this->uniqueSellerSlug($shopName),
            'description'     => null,
            'commission_rate' => (float) setting('default_commission_rate', 10.00),
            'status'          => 'pending',
        ]);
    }

    private function uniqueSellerSlug(string $name): string
    {
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-')) ?: 'shop';
        $slug = $base;
        $i = 1;
        while (Seller::findBySlug($slug)) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }

    // ------------------------------------------------------------------
    //  Logout
    // ------------------------------------------------------------------
    public function logout(): void
    {
        AuditLog::record(Auth::id(), 'auth.logout', 'User logged out');
        Auth::logout();
        Session::setFlash('success', 'You have been logged out.');
        redirect('/');
    }

    // ------------------------------------------------------------------
    //  Password reset
    // ------------------------------------------------------------------
    public function showForgot(): void
    {
        $this->view('auth/forgot', ['title' => 'Reset Password'], 'auth');
    }

    public function sendReset(): void
    {
        $email = strtolower(trim((string) $this->request->input('email', '')));
        $user = $email ? User::findByEmail($email) : null;
        if ($user) {
            $token = bin2hex(random_bytes(24));
            Database::update('users', [
                'reset_token'   => $token,
                'reset_expires' => date('Y-m-d H:i:s', time() + 3600),
            ], ['id' => $user['id']]);

            // Phase 1 has no mail transport; surface the link in development.
            // In production, email this link instead.
            if (APP_ENV === 'development' || APP_DEBUG) {
                Session::setFlash('info', 'DEV MODE: reset link — ' . url('/reset?token=' . $token));
            }
            AuditLog::record((int) $user['id'], 'auth.password_reset_requested', 'Reset link generated');
        }
        // Always show the same message to avoid user enumeration.
        Session::setFlash('success', 'If an account exists for that email, a password reset link has been generated.');
        redirect('/forgot');
    }

    public function showReset(): void
    {
        $token = (string) $this->request->query('token', '');
        $user = $token ? User::findByResetToken($token) : null;
        if (!$user) {
            Session::setFlash('error', 'The reset link is invalid or has expired.');
            redirect('/forgot');
        }
        $this->view('auth/reset', ['title' => 'Set New Password', 'token' => $token], 'auth');
    }

    public function reset(): void
    {
        $token = (string) $this->request->input('token', '');
        $user = User::findByResetToken($token);
        if (!$user) {
            $this->error('The reset link is invalid or has expired.', '/forgot');
        }
        $password = (string) $this->request->input('password', '');
        $confirm  = (string) $this->request->input('password_confirm', '');

        $errors = $this->validate(['password' => $password], ['password' => 'required|min:8|max:72']);
        if ($password !== $confirm) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors['password'] = $errors['password'] ?? 'Password must include letters and numbers.';
        }
        if ($errors) {
            Session::setFlash('errors', $errors);
            redirect('/reset?token=' . urlencode($token));
        }

        Database::update('users', [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'reset_token'   => null,
            'reset_expires' => null,
        ], ['id' => $user['id']]);

        AuditLog::record((int) $user['id'], 'auth.password_reset', 'Password changed via reset');
        Session::setFlash('success', 'Your password has been reset. Please log in.');
        redirect('/login');
    }

    // ------------------------------------------------------------------
    //  Brute-force throttling (session based)
    // ------------------------------------------------------------------
    private function isLockedOut(): bool
    {
        $attempts = Session::get('login_attempts', []);
        $attempts = array_filter($attempts, fn($t) => $t > (time() - self::LOCKOUT_WINDOW));
        Session::set('login_attempts', $attempts);
        return count($attempts) >= self::MAX_ATTEMPTS;
    }

    private function registerFailedAttempt(): void
    {
        $attempts = Session::get('login_attempts', []);
        $attempts[] = time();
        Session::set('login_attempts', $attempts);
    }

    private function clearAttempts(): void
    {
        Session::forget('login_attempts');
    }
}
