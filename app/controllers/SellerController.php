<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\Upload;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Seller;

class SellerController extends \App\Core\Controller
{
    public function __construct()
    {
        parent::__construct();
        Auth::requireRole('seller');
    }

    private function uid(): int
    {
        return (int) Auth::id();
    }

    private function seller(): array
    {
        $seller = Seller::findByUserId($this->uid());
        if (!$seller) {
            $this->abort(403);
        }
        return $seller;
    }

    private function sid(): int
    {
        return (int) $this->seller()['id'];
    }

    // ------------------------------------------------------------------
    //  Dashboard
    // ------------------------------------------------------------------
    public function dashboard(): void
    {
        $user   = current_user();
        $seller = Seller::findByUserId((int) $user['id']);

        $productCount = $seller ? Seller::productCount((int) $seller['id']) : 0;
        $items        = $seller ? OrderItem::forSeller((int) $seller['id']) : [];
        $pending      = count(array_filter($items, fn($i) => in_array($i['order_status'], ['placed', 'confirmed'], true)));
        $earnings     = $seller ? OrderItem::sellerEarnings((int) $seller['id']) : 0.0;
        $recentItems  = array_slice($items, 0, 8);

        $this->view('seller/dashboard', compact('seller', 'productCount', 'pending', 'earnings', 'recentItems'), 'dashboard');
    }

    // ------------------------------------------------------------------
    //  Products (CRUD)
    // ------------------------------------------------------------------
    public function products(): void
    {
        $q = $this->request->query('q', '');
        $products = Product::forSeller($this->sid(), $q);
        $this->view('seller/products/index', compact('products', 'q'), 'dashboard');
    }

    public function createProduct(): void
    {
        $categories = Category::tree();
        $this->view('seller/products/form', ['categories' => $categories, 'product' => null, 'images' => []], 'dashboard');
    }

    public function storeProduct(): void
    {
        $seller = $this->seller();
        $data = $this->request->only(['name', 'category_id', 'brand', 'sku', 'price', 'discount_price', 'stock', 'short_description', 'description', 'specifications']);
        $data = array_merge([
            'name' => '', 'category_id' => '', 'brand' => '', 'sku' => '', 'price' => '',
            'discount_price' => '', 'stock' => '', 'short_description' => '', 'description' => '', 'specifications' => '',
        ], $data);

        $errors = $this->validate($data, [
            'name'        => 'required|min:2|max:200',
            'category_id' => 'required|numeric',
            'price'       => 'required|numeric',
            'stock'       => 'required|numeric',
        ]);
        if ($errors) {
            $this->failWith('/seller/products/create', $errors);
        }

        $price = (float) $data['price'];
        $stock = (int) $data['stock'];
        $discount = ($data['discount_price'] !== '') ? (float) $data['discount_price'] : null;
        if ($discount !== null && $discount >= $price) {
            $this->failWith('/seller/products/create', ['discount_price' => 'Discount must be less than the price.']);
        }

        // Validate + move images first (friendly errors).
        $paths = [];
        try {
            foreach (Upload::restructure($this->request->file('images')) as $f) {
                $paths[] = Upload::image($f, 'products');
            }
        } catch (\Throwable $e) {
            Session::setFlash('errors', ['images' => $e->getMessage()]);
            Session::setFlash('old', $this->request->input());
            redirect('/seller/products/create');
        }

        $status = setting('approval_mode', 'auto') === 'pending' ? 'pending' : 'approved';
        $slug   = Product::uniqueSlug($data['name']);

        try {
            Database::pdo()->beginTransaction();
            $pid = Product::create([
                'seller_id'         => $seller['id'],
                'category_id'       => (int) $data['category_id'],
                'name'              => trim($data['name']),
                'slug'              => $slug,
                'short_description' => $data['short_description'] ?: null,
                'description'       => $data['description'] ?: null,
                'specifications'    => $data['specifications'] ?: null,
                'brand'             => $data['brand'] ?: null,
                'price'             => $price,
                'discount_price'    => $discount,
                'stock'             => $stock,
                'sku'               => $data['sku'] ?: null,
                'status'            => $status,
                'is_featured'       => 0,
            ]);
            foreach ($paths as $i => $path) {
                ProductImage::create([
                    'product_id' => $pid, 'image_path' => $path,
                    'is_primary' => $i === 0 ? 1 : 0, 'sort_order' => $i,
                ]);
            }
            Database::pdo()->commit();
        } catch (\Throwable $e) {
            Database::pdo()->rollBack();
            if (APP_DEBUG) {
                throw $e;
            }
            $this->error('Could not save the product. Please try again.', '/seller/products/create');
        }

        AuditLog::record($this->uid(), 'seller.product.create', "Product #{$pid}");
        $this->success($status === 'pending' ? 'Product submitted for approval.' : 'Product published.', '/seller/products');
    }

    public function editProduct(int $id): void
    {
        $product = Product::findForSeller($id, $this->sid());
        if (!$product) {
            $this->abort(404);
        }
        $images = ProductImage::forProduct($id);
        $categories = Category::tree();
        $this->view('seller/products/form', compact('product', 'images', 'categories'), 'dashboard');
    }

    public function updateProduct(int $id): void
    {
        $product = Product::findForSeller($id, $this->sid());
        if (!$product) {
            $this->abort(404);
        }
        $data = $this->request->only(['name', 'category_id', 'brand', 'sku', 'price', 'discount_price', 'stock', 'short_description', 'description', 'specifications']);
        $data = array_merge([
            'name' => '', 'category_id' => '', 'brand' => '', 'sku' => '', 'price' => '',
            'discount_price' => '', 'stock' => '', 'short_description' => '', 'description' => '', 'specifications' => '',
        ], $data);

        $errors = $this->validate($data, [
            'name'        => 'required|min:2|max:200',
            'category_id' => 'required|numeric',
            'price'       => 'required|numeric',
            'stock'       => 'required|numeric',
        ]);
        if ($errors) {
            $this->failWith('/seller/products/' . $id . '/edit', $errors);
        }

        $price = (float) $data['price'];
        $stock = (int) $data['stock'];
        $discount = ($data['discount_price'] !== '') ? (float) $data['discount_price'] : null;
        if ($discount !== null && $discount >= $price) {
            $this->failWith('/seller/products/' . $id . '/edit', ['discount_price' => 'Discount must be less than the price.']);
        }

        // Remove selected images, then upload new ones.
        $remove = $this->request->input('remove_images', []);
        if (is_array($remove)) {
            foreach ($remove as $rid) {
                if ($rid) {
                    ProductImage::remove((int) $rid);
                }
            }
        }
        $paths = [];
        try {
            foreach (Upload::restructure($this->request->file('images')) as $f) {
                $paths[] = Upload::image($f, 'products');
            }
        } catch (\Throwable $e) {
            Session::setFlash('errors', ['images' => $e->getMessage()]);
            redirect('/seller/products/' . $id . '/edit');
        }

        try {
            Database::pdo()->beginTransaction();
            Product::updateById($id, [
                'category_id'       => (int) $data['category_id'],
                'name'              => trim($data['name']),
                'short_description' => $data['short_description'] ?: null,
                'description'       => $data['description'] ?: null,
                'specifications'    => $data['specifications'] ?: null,
                'brand'             => $data['brand'] ?: null,
                'price'             => $price,
                'discount_price'    => $discount,
                'stock'             => $stock,
                'sku'               => $data['sku'] ?: null,
            ]);
            foreach ($paths as $i => $path) {
                ProductImage::create([
                    'product_id' => $id, 'image_path' => $path,
                    'is_primary' => 0, 'sort_order' => 99 + $i,
                ]);
            }
            $primaryExisting = (int) $this->request->input('primary_image', 0);
            if ($primaryExisting) {
                ProductImage::setPrimary($id, $primaryExisting);
            } else {
                $first = Database::fetch('SELECT id FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order, id LIMIT 1', [$id]);
                if ($first) {
                    ProductImage::setPrimary($id, (int) $first['id']);
                }
            }
            Database::pdo()->commit();
        } catch (\Throwable $e) {
            Database::pdo()->rollBack();
            if (APP_DEBUG) {
                throw $e;
            }
            $this->error('Could not update the product. Please try again.', '/seller/products/' . $id . '/edit');
        }

        AuditLog::record($this->uid(), 'seller.product.update', "Product #{$id}");
        $this->success('Product updated.', '/seller/products');
    }

    public function deleteProduct(int $id): void
    {
        $product = Product::findForSeller($id, $this->sid());
        if (!$product) {
            $this->abort(404);
        }
        Product::softDelete($id);
        AuditLog::record($this->uid(), 'seller.product.delete', "Product #{$id}");
        $this->success('Product removed.', '/seller/products');
    }

    // ------------------------------------------------------------------
    //  Orders
    // ------------------------------------------------------------------
    public function orders(): void
    {
        $orders = OrderItem::ordersForSeller($this->sid());
        $this->view('seller/orders/index', compact('orders'), 'dashboard');
    }

    public function order(int $id): void
    {
        $order = Order::find($id);
        if (!$order) {
            $this->abort(404);
        }
        $items = Database::fetchAll(
            'SELECT * FROM order_items WHERE order_id = ? AND seller_id = ? ORDER BY id',
            [$id, $this->sid()]
        );
        if (!$items) {
            $this->abort(404);
        }
        $this->view('seller/orders/detail', compact('order', 'items'), 'dashboard');
    }

    public function fulfillOrder(int $id): void
    {
        $order = Order::find($id);
        if (!$order) {
            $this->abort(404);
        }
        $status = (string) $this->request->input('fulfillment', 'processing');
        if (!in_array($status, ['processing', 'shipped', 'delivered', 'cancelled'], true)) {
            $status = 'processing';
        }
        Database::query(
            'UPDATE order_items SET fulfillment = ? WHERE order_id = ? AND seller_id = ?',
            [$status, $id, $this->sid()]
        );
        AuditLog::record($this->uid(), 'seller.order.fulfill', "Order #{$id} -> {$status}");
        $this->success('Fulfillment status updated.', '/seller/orders/' . $id);
    }

    // ------------------------------------------------------------------
    //  Shop profile
    // ------------------------------------------------------------------
    public function editShop(): void
    {
        $seller = $this->seller();
        $this->view('seller/shop/edit', compact('seller'), 'dashboard');
    }

    public function updateShop(): void
    {
        $seller = $this->seller();
        $data = $this->request->only(['shop_name', 'description', 'contact_phone', 'contact_email']);
        $data = array_merge(['shop_name' => '', 'description' => '', 'contact_phone' => '', 'contact_email' => ''], $data);
        $errors = $this->validate($data, ['shop_name' => 'required|min:2|max:150']);
        if ($errors) {
            $this->failWith('/seller/shop', $errors);
        }
        $update = [
            'shop_name'      => trim($data['shop_name']),
            'description'    => $data['description'] ?: null,
            'contact_phone'  => $data['contact_phone'] ?: null,
            'contact_email'  => $data['contact_email'] ?: null,
        ];
        try {
            if ($path = Upload::image($this->request->file('shop_logo'), 'shops')) {
                $update['shop_logo'] = $path;
            }
            if ($path = Upload::image($this->request->file('shop_banner'), 'shops')) {
                $update['shop_banner'] = $path;
            }
        } catch (\Throwable $e) {
            Session::setFlash('errors', ['images' => $e->getMessage()]);
            redirect('/seller/shop');
        }
        Seller::updateById($seller['id'], $update);
        AuditLog::record($this->uid(), 'seller.shop.update', "Shop #{$seller['id']}");
        $this->success('Shop profile updated.', '/seller/shop');
    }

    // ------------------------------------------------------------------
    //  Reviews
    // ------------------------------------------------------------------
    public function reviews(): void
    {
        $reviews = Review::forSeller($this->sid());
        $this->view('seller/reviews/index', compact('reviews'), 'dashboard');
    }
}
