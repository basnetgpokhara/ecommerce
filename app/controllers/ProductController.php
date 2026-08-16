<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;

class ProductController extends \App\Core\Controller
{
    /** Product detail page. */
    public function show(string $slug): void
    {
        $product = Product::findBySlug($slug);
        if (!$product
            || $product['status'] !== 'approved'
            || ($product['shop_status'] ?? '') !== 'active'
        ) {
            $this->abort(404);
        }

        $images   = ProductImage::forProduct((int) $product['id']);
        $related  = Product::related((int) $product['id'], $product['category_id'] ? (int) $product['category_id'] : null, 4);
        $reviews  = Review::forProduct((int) $product['id']);

        $this->view('products/show', compact('product', 'images', 'related', 'reviews'));
    }

    /** Search across products. */
    public function search(): void
    {
        $q = trim((string) $this->request->query('q', ''));
        $listing = Product::listing(array_merge($this->request->query, ['q' => $q]));
        $this->view('products/search', compact('listing', 'q'));
    }
}
