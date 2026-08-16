<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Session;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;

class AccountController extends \App\Core\Controller
{
    public function __construct()
    {
        parent::__construct();
        Auth::requireAuth();
    }

    private function uid(): int
    {
        return (int) Auth::id();
    }

    /** Dashboard home — order widgets + recent activity. */
    public function dashboard(): void
    {
        $counts = Order::statusCounts($this->uid());
        $orders = array_slice(Order::forCustomer($this->uid()), 0, 5);
        $this->view('account/dashboard', compact('counts', 'orders'), 'dashboard');
    }

    /** Order history. */
    public function orders(): void
    {
        $orders = Order::forCustomer($this->uid());
        $this->view('account/orders', compact('orders'), 'dashboard');
    }

    /** Order detail. */
    public function order(int $id): void
    {
        $order = Order::findOwned($id, $this->uid());
        if (!$order) {
            $this->abort(404);
        }
        $items    = OrderItem::forOrder($order['id']);
        $payments = Payment::forOrder($order['id']);
        $this->view('account/order_detail', compact('order', 'items', 'payments'), 'dashboard');
    }

    /** Profile edit form. */
    public function profile(): void
    {
        $user = current_user();
        $this->view('account/profile', compact('user'), 'dashboard');
    }

    /** Update profile. */
    public function updateProfile(): void
    {
        $data = $this->request->only(['name', 'phone']);
        $errors = $this->validate($data, [
            'name'  => 'required|min:2|max:120',
            'phone' => 'required|min:7|max:30',
        ]);
        if ($errors) {
            $this->failWith('/account/profile', $errors);
        }
        User::updateById($this->uid(), $data);
        $this->success('Profile updated.', '/account/profile');
    }

    /** Change password. */
    public function changePassword(): void
    {
        $user = current_user();
        $current = (string) $this->request->input('current_password', '');
        $new     = (string) $this->request->input('password', '');
        $confirm = (string) $this->request->input('password_confirm', '');

        $errors = [];
        if (!password_verify($current, $user['password_hash'])) {
            $errors['current_password'] = 'Your current password is incorrect.';
        }
        $e = $this->validate(['password' => $new], ['password' => 'required|min:8|max:72']);
        $errors = array_merge($e, $errors);
        if (!preg_match('/[A-Za-z]/', $new) || !preg_match('/[0-9]/', $new)) {
            $errors['password'] = $errors['password'] ?? 'Password must include letters and numbers.';
        }
        if ($new !== $confirm) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }
        if ($errors) {
            Session::setFlash('errors', $errors);
            redirect('/account/profile');
        }

        User::updateById($this->uid(), [
            'password_hash' => password_hash($new, PASSWORD_DEFAULT),
        ]);
        $this->success('Password changed.', '/account/profile');
    }

    // ------------------------------------------------------------------
    //  Addresses
    // ------------------------------------------------------------------
    public function addresses(): void
    {
        $addresses = Address::forUser($this->uid());
        $this->view('account/addresses', compact('addresses'), 'dashboard');
    }

    public function storeAddress(): void
    {
        $data = $this->request->only(['label', 'full_name', 'phone', 'address_line1', 'address_line2', 'city', 'district', 'postal_code']);
        $errors = $this->validate($data, [
            'full_name'     => 'required|min:2|max:150',
            'phone'         => 'required|min:7|max:30',
            'address_line1' => 'required|max:200',
            'city'          => 'required|max:100',
        ]);
        if ($errors) {
            $this->failWith('/account/addresses', $errors);
        }
        if ($this->request->has('is_default')) {
            Address::clearDefault($this->uid());
            $data['is_default'] = 1;
        }
        Address::create(array_merge($data, ['user_id' => $this->uid()]));
        $this->success('Address added.', '/account/addresses');
    }

    public function updateAddress(int $id): void
    {
        $address = Address::findOwned($id, $this->uid());
        if (!$address) {
            $this->abort(404);
        }
        $data = $this->request->only(['label', 'full_name', 'phone', 'address_line1', 'address_line2', 'city', 'district', 'postal_code']);
        $errors = $this->validate($data, [
            'full_name'     => 'required|min:2|max:150',
            'phone'         => 'required|min:7|max:30',
            'address_line1' => 'required|max:200',
            'city'          => 'required|max:100',
        ]);
        if ($errors) {
            $this->failWith('/account/addresses', $errors);
        }
        if ($this->request->has('is_default')) {
            Address::clearDefault($this->uid());
            $data['is_default'] = 1;
        }
        Address::updateById($id, $data);
        $this->success('Address updated.', '/account/addresses');
    }

    public function deleteAddress(int $id): void
    {
        $address = Address::findOwned($id, $this->uid());
        if (!$address) {
            $this->abort(404);
        }
        Address::deleteById($id);
        $this->success('Address deleted.', '/account/addresses');
    }
}
