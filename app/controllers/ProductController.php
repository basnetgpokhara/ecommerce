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

    /** Customer submits a review for a product (one per customer). */
    public function storeReview(string $slug): void
    {
        \App\Core\Auth::requireAuth();
        if (!\App\Core\Auth::is('customer')) {
            $this->error('Only customer accounts can post reviews.', '/');
        }
        $product = Product::findBySlug($slug);
        if (!$product || $product['status'] !== 'approved') {
            $this->abort(404);
        }
        $rating = (int) $this->request->input('rating', 0);
        $comment = trim((string) $this->request->input('comment', ''));
        if ($rating < 1 || $rating > 5) {
            $this->error('Please choose a star rating.', '/product/' . $slug);
        }
        Review::createReview((int) $product['id'], (int) \App\Core\Auth::id(), $rating, $comment);
        \App\Models\AuditLog::record((int) \App\Core\Auth::id(), 'review.create', "Product #{$product['id']}");
        $this->success('Thanks for your review!', '/product/' . $slug);
    }
}
