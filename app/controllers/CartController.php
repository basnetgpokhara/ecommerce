<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Session;
use App\Models\Cart;
use App\Models\Product;

class CartController extends \App\Core\Controller
{
    public function __construct()
    {
        parent::__construct();
        Auth::requireAuth(); // guests are prompted to log in (PRD permission matrix)
    }

    private function uid(): int
    {
        return (int) Auth::id();
    }

    /** View the cart. */
    public function index(): void
    {
        $items    = Cart::contents($this->uid());
        $subtotal = Cart::subtotal($this->uid());
        $this->view('cart/index', compact('items', 'subtotal'));
    }

    /** Add (or increment) a product. Supports ?buy_now to go straight to checkout. */
    public function add(): void
    {
        $productId = (int) $this->request->input('product_id');
        $qty       = max(1, (int) $this->request->input('quantity', 1));
        $product   = Product::find($productId);

        if (!$product || $product['deleted_at'] || $product['status'] !== 'approved') {
            $this->error('That product is no longer available.', '/cart');
        }
        if ((int) $product['stock'] < 1) {
            $this->error('That product is out of stock.', '/product/' . $product['slug']);
        }

        Cart::add($this->uid(), $productId, $qty);

        if ($this->request->has('buy_now')) {
            redirect('/checkout');
        }
        Session::setFlash('success', 'Added to your cart.');
        redirect('/cart');
    }

    /** Update quantities in bulk. Expects items[]= ['id'=>cart_id, 'quantity'=>n]. */
    public function update(): void
    {
        $items = $this->request->input('items');
        if (is_array($items)) {
            foreach ($items as $row) {
                $cartId = (int) ($row['id'] ?? 0);
                $qty    = (int) ($row['quantity'] ?? 1);
                if ($cartId) {
                    Cart::setQuantity($this->uid(), $cartId, $qty);
                }
            }
        }
        $this->success('Cart updated.', '/cart');
    }

    /** Remove a single line. */
    public function remove(): void
    {
        $cartId = (int) $this->request->input('cart_id');
        Cart::remove($this->uid(), $cartId);
        $this->success('Item removed from your cart.', '/cart');
    }

    /** Empty the cart. */
    public function clear(): void
    {
        Cart::clear($this->uid());
        $this->success('Your cart has been cleared.', '/cart');
    }
}
