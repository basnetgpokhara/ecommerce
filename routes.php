<?php
/**
 * routes.php — central route table.
 * Namespaces follow the PRD: public (/), account (/account/*),
 * seller (/seller/*), admin (/admin/*).
 */

use App\Core\Router;

Router::get('/',        [App\Controllers\HomeController::class, 'index']);

// ---- Public storefront ----
Router::get('/search',     [App\Controllers\ProductController::class, 'search']);
Router::get('/categories', [App\Controllers\CategoryController::class, 'index']);
Router::get('/category/{slug}', [App\Controllers\CategoryController::class, 'show']);
Router::get('/product/{slug}',  [App\Controllers\ProductController::class, 'show']);
Router::get('/shops',      [App\Controllers\ShopController::class, 'index']);
Router::get('/shop/{slug}',[App\Controllers\ShopController::class, 'show']);
Router::get('/page/{slug}',[App\Controllers\PageController::class, 'show']);

// ---- Authentication ----
Router::get('/login',   [App\Controllers\AuthController::class, 'showLogin']);
Router::post('/login',  [App\Controllers\AuthController::class, 'login']);
Router::get('/register',[App\Controllers\AuthController::class, 'showRegister']);
Router::post('/register',[App\Controllers\AuthController::class, 'register']);
Router::get('/logout',  [App\Controllers\AuthController::class, 'logout']);
Router::get('/forgot',  [App\Controllers\AuthController::class, 'showForgot']);
Router::post('/forgot', [App\Controllers\AuthController::class, 'sendReset']);
Router::get('/reset',   [App\Controllers\AuthController::class, 'showReset']);
Router::post('/reset',  [App\Controllers\AuthController::class, 'reset']);

// ---- Cart (login required) ----
Router::get('/cart',          [App\Controllers\CartController::class, 'index']);
Router::post('/cart/add',     [App\Controllers\CartController::class, 'add']);
Router::post('/cart/update',  [App\Controllers\CartController::class, 'update']);
Router::post('/cart/remove',  [App\Controllers\CartController::class, 'remove']);
Router::post('/cart/clear',   [App\Controllers\CartController::class, 'clear']);

// Coupons (Phase 3)
Router::post('/cart/coupon',          [App\Controllers\CartController::class, 'applyCoupon']);
Router::post('/cart/coupon/remove',   [App\Controllers\CartController::class, 'removeCoupon']);

// ---- Checkout (login required) ----
Router::get('/checkout',             [App\Controllers\CheckoutController::class, 'index']);
Router::post('/checkout/place',      [App\Controllers\CheckoutController::class, 'place']);
Router::get('/checkout/success/{id}',[App\Controllers\CheckoutController::class, 'success']);
Router::get('/order/{id}/invoice',   [App\Controllers\CheckoutController::class, 'invoice']);

// ---- Online payments (Phase 3) ----
Router::get('/checkout/pay/{id}',       [App\Controllers\CheckoutController::class, 'pay']);
Router::get('/checkout/failure/{id}',   [App\Controllers\CheckoutController::class, 'failure']);

// Gateway returns / callbacks (external callers — CSRF-exempt, verified server-to-server)
Router::get('/payment/return/{gateway}',  [App\Controllers\PaymentController::class, 'return']);
Router::post('/payment/return/{gateway}', [App\Controllers\PaymentController::class, 'return']);
Router::post('/payment/khalti/verify',    [App\Controllers\PaymentController::class, 'khaltiVerify']);
Router::csrfExempt('/payment/return/{gateway}');
Router::csrfExempt('/payment/khalti/verify');

// ---- Customer dashboard ----
Router::get('/account',                  [App\Controllers\AccountController::class, 'dashboard']);
Router::get('/account/orders',           [App\Controllers\AccountController::class, 'orders']);
Router::get('/account/orders/{id}',      [App\Controllers\AccountController::class, 'order']);
Router::get('/account/profile',          [App\Controllers\AccountController::class, 'profile']);
Router::post('/account/profile',         [App\Controllers\AccountController::class, 'updateProfile']);
Router::post('/account/password',        [App\Controllers\AccountController::class, 'changePassword']);
Router::get('/account/addresses',        [App\Controllers\AccountController::class, 'addresses']);
Router::post('/account/addresses',       [App\Controllers\AccountController::class, 'storeAddress']);
Router::put('/account/addresses/{id}',   [App\Controllers\AccountController::class, 'updateAddress']);
Router::delete('/account/addresses/{id}',[App\Controllers\AccountController::class, 'deleteAddress']);

// ---- Seller dashboard (role: seller) ----
Router::get('/seller', [App\Controllers\SellerController::class, 'dashboard']);

Router::get('/seller/products',          [App\Controllers\SellerController::class, 'products']);
Router::get('/seller/products/create',   [App\Controllers\SellerController::class, 'createProduct']);
Router::post('/seller/products',         [App\Controllers\SellerController::class, 'storeProduct']);
Router::get('/seller/products/{id}/edit',[App\Controllers\SellerController::class, 'editProduct']);
Router::post('/seller/products/{id}',    [App\Controllers\SellerController::class, 'updateProduct']);
Router::post('/seller/products/{id}/delete', [App\Controllers\SellerController::class, 'deleteProduct']);

Router::get('/seller/orders',            [App\Controllers\SellerController::class, 'orders']);
Router::get('/seller/orders/{id}',       [App\Controllers\SellerController::class, 'order']);
Router::post('/seller/orders/{id}/fulfill', [App\Controllers\SellerController::class, 'fulfillOrder']);

Router::get('/seller/shop',              [App\Controllers\SellerController::class, 'editShop']);
Router::post('/seller/shop',             [App\Controllers\SellerController::class, 'updateShop']);

Router::get('/seller/reviews',           [App\Controllers\SellerController::class, 'reviews']);

// ---- Admin dashboard (role: admin) ----
Router::get('/admin', [App\Controllers\AdminController::class, 'dashboard']);

// Users
Router::get('/admin/users',                  [App\Controllers\AdminController::class, 'users']);
Router::post('/admin/users/{id}/status',     [App\Controllers\AdminController::class, 'userStatus']);
Router::post('/admin/users/{id}/delete',     [App\Controllers\AdminController::class, 'userDelete']);

// Sellers
Router::get('/admin/sellers',                [App\Controllers\AdminController::class, 'sellers']);
Router::post('/admin/sellers/{id}/approve',  [App\Controllers\AdminController::class, 'sellerApprove']);
Router::post('/admin/sellers/{id}/suspend',  [App\Controllers\AdminController::class, 'sellerSuspend']);
Router::get('/admin/sellers/{id}/edit',      [App\Controllers\AdminController::class, 'sellerEdit']);
Router::post('/admin/sellers/{id}',          [App\Controllers\AdminController::class, 'sellerUpdate']);

// Products
Router::get('/admin/products',                   [App\Controllers\AdminController::class, 'products']);
Router::post('/admin/products/{id}/approve',     [App\Controllers\AdminController::class, 'productApprove']);
Router::post('/admin/products/{id}/reject',      [App\Controllers\AdminController::class, 'productReject']);
Router::post('/admin/products/{id}/feature',     [App\Controllers\AdminController::class, 'productFeature']);
Router::post('/admin/products/{id}/delete',      [App\Controllers\AdminController::class, 'productDelete']);

// Categories
Router::get('/admin/categories',                 [App\Controllers\AdminController::class, 'categories']);
Router::post('/admin/categories',                [App\Controllers\AdminController::class, 'categoryStore']);
Router::post('/admin/categories/{id}',           [App\Controllers\AdminController::class, 'categoryUpdate']);
Router::post('/admin/categories/{id}/delete',    [App\Controllers\AdminController::class, 'categoryDelete']);

// Orders
Router::get('/admin/orders',                     [App\Controllers\AdminController::class, 'orders']);
Router::get('/admin/orders/{id}',                [App\Controllers\AdminController::class, 'order']);
Router::post('/admin/orders/{id}/status',        [App\Controllers\AdminController::class, 'orderStatus']);

// Reviews (moderation)
Router::get('/admin/reviews',                    [App\Controllers\AdminController::class, 'reviews']);
Router::post('/admin/reviews/{id}/{status}',     [App\Controllers\AdminController::class, 'reviewSet']);

// Banners (homepage CMS)
Router::get('/admin/banners',                    [App\Controllers\AdminController::class, 'banners']);
Router::post('/admin/banners',                   [App\Controllers\AdminController::class, 'bannerStore']);
Router::post('/admin/banners/{id}/delete',       [App\Controllers\AdminController::class, 'bannerDelete']);

// Pages (static CMS)
Router::get('/admin/pages',                      [App\Controllers\AdminController::class, 'pages']);
Router::post('/admin/pages',                     [App\Controllers\AdminController::class, 'pageStore']);
Router::post('/admin/pages/{id}',                [App\Controllers\AdminController::class, 'pageUpdate']);
Router::post('/admin/pages/{id}/delete',         [App\Controllers\AdminController::class, 'pageDelete']);

// Coupons (Phase 3)
Router::get('/admin/coupons',                    [App\Controllers\AdminController::class, 'coupons']);
Router::post('/admin/coupons',                   [App\Controllers\AdminController::class, 'couponStore']);
Router::post('/admin/coupons/{id}',              [App\Controllers\AdminController::class, 'couponUpdate']);
Router::post('/admin/coupons/{id}/delete',       [App\Controllers\AdminController::class, 'couponDelete']);

// Analytics & reports (Phase 4)
Router::get('/admin/analytics',                  [App\Controllers\AdminController::class, 'analytics']);

// Settings + audit log
Router::get('/admin/settings',                   [App\Controllers\AdminController::class, 'settings']);
Router::post('/admin/settings',                  [App\Controllers\AdminController::class, 'settingsSave']);
Router::get('/admin/audit',                      [App\Controllers\AdminController::class, 'audit']);

// ---- Customer reviews ----
Router::post('/product/{slug}/review',           [App\Controllers\ProductController::class, 'storeReview']);
Router::post('/account/reviews/{id}/delete',     [App\Controllers\AccountController::class, 'destroyReview']);
