<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;

class SellerController extends \App\Core\Controller
{
    public function __construct()
    {
        parent::__construct();
        Auth::requireRole('seller');
    }

    /** Seller dashboard home — overview widgets (full management lands in Phase 2). */
    public function dashboard(): void
    {
        $user   = current_user();
        $seller = Seller::findByUserId((int) $user['id']);

        $productCount = $seller ? Seller::productCount((int) $seller['id']) : 0;
        $items        = $seller ? OrderItem::forSeller((int) $seller['id']) : [];
        $pending      = count(array_filter($items, fn($i) => in_array($i['order_status'], ['placed', 'confirmed'], true)));
        $earnings     = $seller ? OrderItem::sellerEarnings((int) $seller['id']) : 0.0;

        $recentItems = array_slice($items, 0, 8);

        $this->view('seller/dashboard', compact('seller', 'productCount', 'pending', 'earnings', 'recentItems'), 'dashboard');
    }
}
