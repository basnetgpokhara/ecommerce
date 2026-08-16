<?php
namespace App\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;

class HomeController extends \App\Core\Controller
{
    /** Homepage with hero, categories, product sections and shops. */
    public function index(): void
    {
        $banners     = Banner::active('hero');
        $promos      = Banner::active('promo');
        $categories  = Category::featured();
        if (!$categories) {
            $categories = Category::roots();
        }
        $featured    = Product::featured(8);
        $newArrivals = Product::newArrivals(8);
        $deals       = Product::deals(4);
        $shops       = array_slice(Seller::active(), 0, 6);

        $this->view('home/index', compact(
            'banners', 'promos', 'categories', 'featured', 'newArrivals', 'deals', 'shops'
        ));
    }
}
