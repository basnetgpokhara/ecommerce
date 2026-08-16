<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Seller;

class ShopController extends \App\Core\Controller
{
    /** Directory of active seller shops. */
    public function index(): void
    {
        $shops = Seller::active();
        $this->view('shops/index', compact('shops'));
    }

    /** A single shop with its approved products. */
    public function show(string $slug): void
    {
        $seller = Seller::findBySlug($slug);
        if (!$seller || $seller['status'] !== 'active') {
            $this->abort(404);
        }
        $products = Product::bySeller((int) $seller['id'], 50);
        $this->view('shops/show', compact('seller', 'products'));
    }
}
