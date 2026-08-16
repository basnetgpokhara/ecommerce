<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Gateway;
use App\Core\Session;
use App\Models\Address;
use App\Models\AuditLog;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Seller;

class CheckoutController extends \App\Core\Controller
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

    /** Show the checkout form (addresses, summary, payment methods). */
    public function index(): void
    {
        $items = Cart::contents($this->uid());
        if (!$items) {
            $this->error('Your cart is empty.', '/cart');
        }
        $addresses       = Address::forUser($this->uid());
        $subtotal        = Cart::subtotal($this->uid());
        $discount        = $this->appliedCouponDiscount($subtotal);
        $shippingFee     = $this->shippingFee($subtotal);
        $total           = round($subtotal - $discount + $shippingFee, 2);
        $coupon          = $this->appliedCoupon();
        $paymentMethods  = $this->enabledPaymentMethods();

        $this->view('checkout/index', compact(
            'items', 'addresses', 'subtotal', 'discount', 'coupon',
            'shippingFee', 'total', 'paymentMethods'
        ));
    }

    /** Place the order. COD is finalised here; online gateways hand off to /checkout/pay. */
    public function place(): void
    {
        $userId = $this->uid();
        $items  = Cart::contents($userId);

        if (!$items) {
            $this->error('Your cart is empty.', '/cart');
        }
        if (!Cart::isBuyable($userId)) {
            $this->error('Some items are no longer available. Please review your cart.', '/cart');
        }

        // ---- Resolve shipping address ----
        $addressId = (int) $this->request->input('address_id');
        $address   = $addressId ? Address::findOwned($addressId, $userId) : null;

        if (!$address) {
            $newAddr = $this->request->only(['full_name', 'phone', 'address_line1', 'address_line2', 'city', 'district', 'postal_code', 'label']);
            $errors = $this->validate($newAddr, [
                'full_name'     => 'required|min:2|max:150',
                'phone'         => 'required|min:7|max:30',
                'address_line1' => 'required|max:200',
                'city'          => 'required|max:100',
            ]);
            if ($errors) {
                $this->failWith('/checkout', $errors);
            }
            $addressId = Address::create(array_merge($newAddr, ['user_id' => $userId]));
            $address   = Address::find($addressId);
        }

        // ---- Payment method ----
        $method = $this->request->input('payment_method', 'cod');
        $allowed = array_keys($this->enabledPaymentMethods());
        if (!in_array($method, $allowed, true)) {
            $this->error('Please choose a valid payment method.', '/checkout');
        }

        // ---- Totals (with any applied coupon) ----
        $subtotal    = Cart::subtotal($userId);
        $coupon      = $this->appliedCoupon();
        $discount    = $coupon ? $this->couponDiscount($coupon, $subtotal) : 0.0;
        $shippingFee = $this->shippingFee($subtotal);
        $total       = round($subtotal - $discount + $shippingFee, 2);
        $orderNumber = Order::generateNumber();

        // ---- Persist order transactionally ----
        try {
            Database::pdo()->beginTransaction();

            $orderId = Order::create([
                'order_number'      => $orderNumber,
                'customer_id'       => $userId,
                'shipping_name'     => $address['full_name'],
                'shipping_phone'    => $address['phone'],
                'shipping_address'  => trim($address['address_line1'] . "\n" . $address['address_line2']),
                'shipping_city'     => $address['city'],
                'shipping_district' => $address['district'] ?? null,
                'subtotal'          => $subtotal,
                'shipping_fee'      => $shippingFee,
                'discount'          => $discount,
                'tax'               => 0,
                'total'             => $total,
                'status'            => 'placed',
                'payment_method'    => $method,
                'payment_status'    => 'pending',
                'coupon_code'       => $coupon ? $coupon['code'] : null,
                'notes'             => $this->request->input('notes'),
            ]);

            foreach ($items as $it) {
                $product = Product::find((int) $it['product_id']);
                $seller  = $product ? Seller::find((int) $product['seller_id']) : null;
                $qty     = (int) $it['quantity'];
                $unit    = (float) $it['effective_price'];
                $line    = round($unit * $qty, 2);
                $rate    = $seller ? (float) $seller['commission_rate'] : 0.0;
                $comm    = round($line * $rate / 100, 2);
                $earn    = round($line - $comm, 2);

                OrderItem::create([
                    'order_id'          => $orderId,
                    'product_id'        => $product['id'] ?? null,
                    'seller_id'         => $seller['id'] ?? 0,
                    'product_name'      => $it['name'],
                    'product_image'     => $it['image'] ?? null,
                    'quantity'          => $qty,
                    'unit_price'        => $unit,
                    'subtotal'          => $line,
                    'commission_rate'   => $rate,
                    'commission_amount' => $comm,
                    'seller_earnings'   => $earn,
                ]);

                Product::decrementStock((int) $it['product_id'], $qty);
            }

            Payment::create([
                'order_id' => $orderId,
                'gateway'  => $method,
                'amount'   => $total,
                'status'   => 'initiated',
            ]);

            Database::pdo()->commit();
        } catch (\Throwable $e) {
            Database::pdo()->rollBack();
            if (APP_DEBUG) {
                throw $e;
            }
            $this->error('Sorry, we could not place your order. Please try again.', '/checkout');
        }

        Cart::clear($userId);
        if ($coupon) {
            Coupon::incrementUsage((int) $coupon['id']);
        }
        Session::forget('coupon_code');
        AuditLog::record($userId, 'order.placed', "Order {$orderNumber} placed");

        if ($method === 'cod') {
            redirect('/checkout/success/' . $orderId);
        }
        // Online gateway: send the customer to the payment initiation page.
        redirect('/checkout/pay/' . $orderId);
    }

    /** Send the customer to the selected online gateway to pay. */
    public function pay(int $id): void
    {
        $order = Order::findOwned($id, $this->uid());
        if (!$order) {
            $this->abort(404);
        }
        $gateway = Gateway::make((string) $order['payment_method']);
        if (!$gateway || !$gateway->isEnabled()) {
            $this->error('That payment gateway is no longer available.', '/checkout');
        }
        if ($order['payment_status'] === 'paid') {
            redirect('/checkout/success/' . $id);
        }
        $payload = $gateway->initiate($order);
        if (!empty($payload['error'])) {
            $this->error($payload['error'], '/checkout');
        }
        $this->view('checkout/gateway', compact('order', 'gateway', 'payload'), 'minimal');
    }

    /** Payment failed / cancelled page. */
    public function failure(int $id): void
    {
        $order = Order::findOwned($id, $this->uid());
        if (!$order) {
            $this->abort(404);
        }
        $payments = Payment::forOrder($order['id']);
        $this->view('checkout/failure', compact('order', 'payments'));
    }

    /** Order confirmation page. */
    public function success(int $id): void
    {
        $order = Order::findOwned($id, $this->uid());
        if (!$order) {
            $this->abort(404);
        }
        $items    = OrderItem::forOrder($order['id']);
        $payments = Payment::forOrder($order['id']);
        $this->view('checkout/success', compact('order', 'items', 'payments'));
    }

    /** Print-friendly A4 invoice (browser "Save as PDF"). */
    public function invoice(int $id): void
    {
        $order = Order::findOwned($id, $this->uid());
        if (!$order) {
            $this->abort(404);
        }
        $items = OrderItem::forOrder($order['id']);
        $this->view('checkout/invoice', compact('order', 'items'), 'minimal');
    }

    // ------------------------------------------------------------------
    /** Coupon currently applied in the session, or null if invalid for $subtotal. */
    private function appliedCoupon(): ?array
    {
        $code = (string) Session::get('coupon_code', '');
        if ($code === '') {
            return null;
        }
        $coupon = Coupon::findByCode($code);
        return $coupon ?: null;
    }

    private function couponDiscount(array $coupon, float $subtotal): float
    {
        return Coupon::isValid($coupon, $subtotal) ? Coupon::discountFor($coupon, $subtotal) : 0.0;
    }

    private function appliedCouponDiscount(float $subtotal): float
    {
        $coupon = $this->appliedCoupon();
        return $coupon ? $this->couponDiscount($coupon, $subtotal) : 0.0;
    }

    private function shippingFee(float $subtotal): float
    {
        $threshold = (float) setting('free_shipping_threshold', 0);
        $fee       = (float) setting('shipping_fee', 0);
        if ($threshold > 0 && $subtotal >= $threshold) {
            return 0.0;
        }
        return $fee;
    }

    private function enabledPaymentMethods(): array
    {
        $methods = [];
        if (setting('cod_enabled', '1') !== '0') {
            $methods['cod'] = 'Cash on Delivery';
        }
        foreach (Gateway::enabled() as $code => $gateway) {
            $methods[$code] = $gateway->label();
        }
        if (!$methods) {
            $methods['cod'] = 'Cash on Delivery';
        }
        return $methods;
    }
}
