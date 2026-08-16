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

// ---- Checkout (login required) ----
Router::get('/checkout',             [App\Controllers\CheckoutController::class, 'index']);
Router::post('/checkout/place',      [App\Controllers\CheckoutController::class, 'place']);
Router::get('/checkout/success/{id}',[App\Controllers\CheckoutController::class, 'success']);
Router::get('/order/{id}/invoice',   [App\Controllers\CheckoutController::class, 'invoice']);

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

// ---- Admin dashboard (role: admin) ----
Router::get('/admin', [App\Controllers\AdminController::class, 'dashboard']);
