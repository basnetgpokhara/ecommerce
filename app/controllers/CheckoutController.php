<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Address;
use App\Models\AuditLog;
use App\Models\Cart;
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
        $shippingFee     = $this->shippingFee($subtotal);
        $total           = $subtotal + $shippingFee;
        $paymentMethods  = $this->enabledPaymentMethods();

        $this->view('checkout/index', compact(
            'items', 'addresses', 'subtotal', 'shippingFee', 'total', 'paymentMethods'
        ));
    }

    /** Place the order (COD in Phase 1; gateways are stubbed for Phase 3). */
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
        if ($method !== 'cod') {
            $this->error('Online payment gateways are enabled in Phase 3. Please choose Cash on Delivery.', '/checkout');
        }

        // ---- Totals ----
        $subtotal    = Cart::subtotal($userId);
        $shippingFee = $this->shippingFee($subtotal);
        $total       = $subtotal + $shippingFee;
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
                'discount'          => 0,
                'tax'               => 0,
                'total'             => $total,
                'status'            => 'placed',
                'payment_method'    => $method,
                'payment_status'    => 'pending',
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
        AuditLog::record($userId, 'order.placed', "Order {$orderNumber} placed");
        redirect('/checkout/success/' . $orderId);
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
        if (setting('esewa_enabled', '0') === '1') {
            $methods['esewa'] = 'eSewa';
        }
        if (setting('khalti_enabled', '0') === '1') {
            $methods['khalti'] = 'Khalti';
        }
        if (setting('fonepay_enabled', '0') === '1') {
            $methods['fonepay'] = 'Fonepay';
        }
        if (!$methods) {
            $methods['cod'] = 'Cash on Delivery';
        }
        return $methods;
    }
}
