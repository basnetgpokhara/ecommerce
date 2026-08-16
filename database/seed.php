<?php
/**
 * database/seed.php — demo data seeder (CLI).
 *
 * Run after importing database/schema.sql:
 *     php database/seed.php
 *
 * Safe to run repeatedly (upserts / existence-checked inserts).
 */
require __DIR__ . '/../config/config.php';

use App\Core\Database;

$pdo = Database::pdo();
$now = date('Y-m-d H:i:s');

$out = function (string $msg): void { echo $msg . "\n"; };

// ---------------------------------------------------------------- helpers
function settings_set(string $k, string $v): void
{
    Database::query(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
        [$k, $v]
    );
}
function email_exists(string $email): bool
{
    return (bool) Database::fetch('SELECT id FROM users WHERE email = ?', [$email]);
}
function page_upsert(string $title, string $slug, string $body): void
{
    Database::query(
        "INSERT INTO pages (title, slug, body, status) VALUES (?, ?, ?, 'published')
         ON DUPLICATE KEY UPDATE title = VALUES(title), body = VALUES(body)",
        [$title, $slug, $body]
    );
}

// ---------------------------------------------------------------- settings
$out('→ settings');
foreach ([
    'site_name'               => 'NepMart Marketplace',
    'site_tagline'            => 'A modern multi-vendor marketplace connecting local sellers and shoppers across Nepal.',
    'currency_code'           => 'NPR',
    'currency_symbol'         => 'रू',
    'contact_email'           => 'hello@nepmart.test',
    'contact_phone'           => '+977 1-590-0000',
    'approval_mode'           => 'auto',   // auto | pending (configurable)
    'default_commission_rate' => '10.00',  // per-seller commission
    'cod_enabled'             => '1',
    'esewa_enabled'           => '0',      // Phase 3
    'khalti_enabled'          => '0',      // Phase 3
    'fonepay_enabled'         => '0',      // Phase 3
    'free_shipping_threshold' => '2000',
    'shipping_fee'            => '150',
    'review_auto_approve'     => '1',
] as $k => $v) {
    settings_set($k, $v);
}

// ---------------------------------------------------------------- CMS pages
$out('→ pages');
page_upsert('About Us', 'about', '<p>NepMart is a multi-vendor marketplace built to empower Nepali sellers and make online shopping effortless for customers nationwide.</p>');
page_upsert('Privacy Policy', 'privacy', '<p>We respect your privacy. Your personal data is only used to fulfil orders and improve your shopping experience.</p>');
page_upsert('Terms &amp; Conditions', 'terms', '<p>By using NepMart you agree to our terms of service. Sellers are responsible for the accuracy of their listings.</p>');
page_upsert('Returns &amp; Refunds', 'returns', '<p>Items can be returned within 7 days of delivery if unused and in original packaging. Refunds are processed to your original payment method.</p>');
page_upsert('Shipping', 'shipping', '<p>We deliver across Nepal. Orders over रू 2,000 ship free. Standard delivery takes 2–5 business days.</p>');
page_upsert('Contact Us', 'contact', '<p>Reach us at hello@nepmart.test or +977 1-590-0000. We’re here to help 7 days a week.</p>');

// ---------------------------------------------------------------- banners
$out('→ banners');
Database::query('DELETE FROM banners');
$banners = [
    ['Big Sale, Local Shops', 'Up to 40% off electronics, fashion & more', 'assets/img/hero1.svg', '/search?sort=newest', 'Shop Now', 'hero', 0],
    ['Fresh from Nepal', 'Groceries & home essentials, delivered fast', 'assets/img/hero2.svg', '/category/grocery', 'Shop Groceries', 'hero', 1],
    ['Free shipping over रू 2,000', 'Pay with eSewa, Khalti, Fonepay or COD', null, '/shops', 'Explore Shops', 'promo', 0],
];
foreach ($banners as $b) {
    Database::insert('banners', [
        'title' => $b[0], 'subtitle' => $b[1], 'image' => $b[2],
        'link' => $b[3], 'button_text' => $b[4], 'position' => $b[5],
        'sort_order' => $b[6], 'status' => 'active',
    ]);
}

// ---------------------------------------------------------------- users
$out('→ users');
$pw = function (string $p): string { return password_hash($p, PASSWORD_DEFAULT); };

if (!email_exists('admin@nepmart.test')) {
    Database::insert('users', [
        'name' => 'Site Admin', 'email' => 'admin@nepmart.test', 'phone' => '9800000001',
        'password_hash' => $pw('admin123'), 'role' => 'admin', 'status' => 'active',
    ]);
}
$sellersSeed = [
    ['Himalayan Gadgets', 'himgadgets@nepmart.test', '9800000002', 'himalayan-gadgets', 'assets/img/shop-banner-1.svg', 'Electronics & gadgets from across Nepal.', 10.00],
    ['Annapurna Apparel', 'apparel@nepmart.test',   '9800000003', 'annapurna-apparel', 'assets/img/shop-banner-2.svg', 'Fashion and footwear for every season.', 12.00],
    ['Everest Essentials','everest@nepmart.test',   '9800000004', 'everest-essentials','assets/img/shop-banner-3.svg', 'Home, grocery and daily essentials.', 8.00],
];
$sellerIds = [];
foreach ($sellersSeed as $s) {
    if (!email_exists($s[1])) {
        $uid = Database::insert('users', [
            'name' => $s[0], 'email' => $s[1], 'phone' => $s[2],
            'password_hash' => $pw('seller123'), 'role' => 'seller', 'status' => 'active',
        ]);
    } else {
        $u = Database::fetch('SELECT id FROM users WHERE email = ?', [$s[1]]);
        $uid = (int) $u['id'];
    }
    if (!Database::fetch('SELECT id FROM sellers WHERE slug = ?', [$s[3]])) {
        Database::insert('sellers', [
            'user_id' => $uid, 'shop_name' => $s[0], 'slug' => $s[3], 'shop_banner' => $s[4],
            'description' => $s[5], 'commission_rate' => $s[6], 'status' => 'active',
            'contact_email' => $s[1], 'contact_phone' => $s[2],
        ]);
    }
    $sellerIds[$s[3]] = (int) Database::fetch('SELECT id FROM sellers WHERE slug = ?', [$s[3]])['id'];
}
if (!email_exists('buyer@nepmart.test')) {
    $customerId = Database::insert('users', [
        'name' => 'Rita Sharma', 'email' => 'buyer@nepmart.test', 'phone' => '9800000009',
        'password_hash' => $pw('customer123'), 'role' => 'customer', 'status' => 'active',
    ]);
} else {
    $customerId = (int) Database::fetch('SELECT id FROM users WHERE email = ?', ['buyer@nepmart.test'])['id'];
}

// ---------------------------------------------------------------- categories
$out('→ categories');
$cats = [
    ['Electronics',     'electronics',     'assets/img/cat-electronics.svg', 0, 1],
    ['Fashion',         'fashion',         'assets/img/cat-fashion.svg',     1, 1],
    ['Home & Living',   'home-living',     'assets/img/cat-home.svg',        2, 1],
    ['Beauty & Health', 'beauty-health',   'assets/img/cat-beauty.svg',      3, 1],
    ['Sports',          'sports',          'assets/img/cat-sports.svg',      4, 1],
    ['Grocery',         'grocery',         'assets/img/cat-grocery.svg',     5, 1],
];
$catId = [];
foreach ($cats as $c) {
    if (!Database::fetch('SELECT id FROM categories WHERE slug = ?', [$c[1]])) {
        Database::insert('categories', [
            'name' => $c[0], 'slug' => $c[1], 'image' => $c[2],
            'sort_order' => $c[3], 'is_featured' => $c[4],
        ]);
    }
    $catId[$c[1]] = (int) Database::fetch('SELECT id FROM categories WHERE slug = ?', [$c[1]])['id'];
}
// a few sub-categories
foreach ([
    ['Mobiles', 'mobiles', $catId['electronics']],
    ['Computers', 'computers', $catId['electronics']],
    ['Clothing', 'clothing', $catId['fashion']],
    ['Footwear', 'footwear', $catId['fashion']],
] as $sub) {
    if (!Database::fetch('SELECT id FROM categories WHERE slug = ?', [$sub[1]])) {
        Database::insert('categories', ['name' => $sub[0], 'slug' => $sub[1], 'parent_id' => $sub[2], 'sort_order' => 0, 'is_featured' => 0]);
    }
}

// ---------------------------------------------------------------- products
$out('→ products');
$products = [
    // name, slug, sellerSlug, catSlug, sku, price, discount, stock, brand, image, featured, rating_avg, rating_count
    ['Galaxy A55 Smartphone',    'galaxy-a55',      'himalayan-gadgets', 'electronics', 'HG-A55',   28990, 25990, 25, 'Samsung',  'assets/img/phone.svg',     1, 4.5, 18],
    ['14" Ultrabook Laptop',     'ultrabook-14',    'himalayan-gadgets', 'electronics', 'HG-L14',   78990, 72990, 10, 'Lenovo',   'assets/img/laptop.svg',    1, 4.7, 9],
    ['Wireless ANC Headphones',  'anc-headphones',  'himalayan-gadgets', 'electronics', 'HG-HP1',   7990,  5990,  40, 'Sony',     'assets/img/headphones.svg',1, 4.4, 26],
    ['Smartwatch Series 7',      'smartwatch-7',    'himalayan-gadgets', 'electronics', 'HG-SW7',   14990, 12990, 18, 'Amazfit',  'assets/img/watch.svg',     0, 4.2, 12],
    ['Himalayan Trail Sneakers', 'trail-sneakers',  'annapurna-apparel', 'sports',      'AA-TS1',   4990,  3990,  30, 'Adidas',   'assets/img/shoes.svg',     1, 4.6, 21],
    ['Down Winter Jacket',       'winter-jacket',   'annapurna-apparel', 'fashion',     'AA-WJ1',   6990,  4990,  15, 'Northface','assets/img/jacket.svg',    1, 4.3, 14],
    ['Organic Cotton T-Shirt',   'cotton-tshirt',   'annapurna-apparel', 'fashion',     'AA-TS2',   1290,  0,     60, 'Annapurna','assets/img/tshirt.svg',    0, 4.1, 7],
    ['Basmati Rice 5kg',         'basmati-rice-5kg','everest-essentials','grocery',     'EE-RICE',  990,   849,   100,'Everest',  'assets/img/rice.svg',      0, 4.0, 33],
    ['LED Desk Lamp',            'led-desk-lamp',   'everest-essentials','home-living', 'EE-LMP',   1990,  1490,  22, 'Philips',  'assets/img/lamp.svg',      0, 4.2, 5],
    ['Power Blender 1.5L',       'power-blender',   'everest-essentials','home-living', 'EE-BLD',   5490,  4790,  14, 'Philips',  'assets/img/blender.svg',   1, 4.5, 11],
    ['Matte Lipstick Set',       'lipstick-set',    'everest-essentials','beauty-health','EE-LIP',  1890,  1490,  35, 'Maybelline','assets/img/lipstick.svg', 0, 4.4, 19],
    ['Match Football Size 5',    'match-football',  'annapurna-apparel', 'sports',      'AA-FB5',   2490,  1990,  20, 'Nike',     'assets/img/football.svg',  0, 4.3, 8],
];
$productIds = [];
foreach ($products as $p) {
    [$name,$slug,$seller,$cat,$sku,$price,$disc,$stock,$brand,$img,$feat,$ra,$rc] = $p;
    if (Database::fetch('SELECT id FROM products WHERE slug = ?', [$slug])) {
        $productIds[$slug] = (int) Database::fetch('SELECT id FROM products WHERE slug = ?', [$slug])['id'];
        continue;
    }
    $pid = Database::insert('products', [
        'seller_id'        => $sellerIds[$seller],
        'category_id'      => $catId[$cat],
        'name'             => $name,
        'slug'             => $slug,
        'short_description'=> substr($name . ' — quality product from ' . $seller, 0, 280),
        'description'      => "High-quality {$name}, sourced and shipped within Nepal. Backed by seller warranty and easy returns.\n\n• Genuine product\n• Fast nationwide delivery\n• 7-day return policy",
        'specifications'   => "Brand: {$brand}\nSKU: {$sku}\nWarranty: 1 year",
        'brand'            => $brand,
        'price'            => $price,
        'discount_price'   => $disc > 0 ? $disc : null,
        'stock'            => $stock,
        'sku'              => $sku,
        'status'           => 'approved',
        'is_featured'      => $feat,
        'rating_avg'       => $ra,
        'rating_count'     => $rc,
    ]);
    Database::insert('product_images', [
        'product_id' => $pid, 'image_path' => $img, 'is_primary' => 1, 'sort_order' => 0,
    ]);
    $productIds[$slug] = $pid;
}

// ---------------------------------------------------------------- address + sample order
$out('→ sample address + order');
Database::query('DELETE FROM addresses WHERE user_id = ?', [$customerId]);
$addrId = Database::insert('addresses', [
    'user_id' => $customerId, 'label' => 'Home', 'full_name' => 'Rita Sharma',
    'phone' => '9800000009', 'address_line1' => 'Lakeside Road', 'address_line2' => 'Near Phewa Lake',
    'city' => 'Pokhara', 'district' => 'Kaski', 'postal_code' => '33700', 'is_default' => 1,
]);

// only create the sample order if the customer has none yet
if (!Database::fetch('SELECT id FROM orders WHERE customer_id = ?', [$customerId])) {
    $lines = [
        ['galaxy-a55', 1],
        ['trail-sneakers', 2],
    ];
    $subtotal = 0.0;
    $rows = [];
    foreach ($lines as [$slug, $qty]) {
        $prod = Database::fetch('SELECT p.*, s.commission_rate, s.id AS seller_id FROM products p JOIN sellers s ON s.id=p.seller_id WHERE p.slug = ?', [$slug]);
        $unit = $prod['discount_price'] ? (float)$prod['discount_price'] : (float)$prod['price'];
        $line = round($unit * $qty, 2);
        $subtotal += $line;
        $rows[] = ['prod' => $prod, 'qty' => $qty, 'unit' => $unit, 'line' => $line];
    }
    $shipping = 0.0;
    $total = $subtotal + $shipping;
    $orderNumber = 'NM-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    $orderId = Database::insert('orders', [
        'order_number' => $orderNumber, 'customer_id' => $customerId,
        'shipping_name' => 'Rita Sharma', 'shipping_phone' => '9800000009',
        'shipping_address' => "Lakeside Road\nNear Phewa Lake", 'shipping_city' => 'Pokhara',
        'shipping_district' => 'Kaski', 'subtotal' => $subtotal, 'shipping_fee' => $shipping,
        'discount' => 0, 'tax' => 0, 'total' => $total, 'status' => 'confirmed',
        'payment_method' => 'cod', 'payment_status' => 'pending',
        'created_at' => $now,
    ]);
    foreach ($rows as $r) {
        $comm = round($r['line'] * (float)$r['prod']['commission_rate'] / 100, 2);
        Database::insert('order_items', [
            'order_id' => $orderId, 'product_id' => $r['prod']['id'], 'seller_id' => $r['prod']['seller_id'],
            'product_name' => $r['prod']['name'],
            'product_image' => Database::fetch('SELECT image_path FROM product_images WHERE product_id = ? LIMIT 1', [$r['prod']['id']])['image_path'] ?? null,
            'quantity' => $r['qty'], 'unit_price' => $r['unit'], 'subtotal' => $r['line'],
            'commission_rate' => (float)$r['prod']['commission_rate'], 'commission_amount' => $comm,
            'seller_earnings' => round($r['line'] - $comm, 2),
            'created_at' => $now,
        ]);
    }
    Database::insert('payments', [
        'order_id' => $orderId, 'gateway' => 'cod', 'amount' => $total, 'status' => 'initiated', 'created_at' => $now,
    ]);
}

// ---------------------------------------------------------------- a couple of reviews
$out('→ reviews');
if (!Database::fetch('SELECT id FROM reviews WHERE product_id = ?', [$productIds['galaxy-a55']])) {
    Database::insert('reviews', ['product_id' => $productIds['galaxy-a55'], 'customer_id' => $customerId, 'rating' => 5, 'comment' => 'Great phone, delivered quickly to Pokhara!', 'status' => 'approved']);
}
if (!Database::fetch('SELECT id FROM reviews WHERE product_id = ?', [$productIds['trail-sneakers']])) {
    Database::insert('reviews', ['product_id' => $productIds['trail-sneakers'], 'customer_id' => $customerId, 'rating' => 4, 'comment' => 'Comfortable and sturdy. Good value.', 'status' => 'approved']);
}

// ---------------------------------------------------------------- done
$out('');
$out('✅ Seed complete.');
$out('');
$out('Demo accounts (email / password):');
$out('  Admin     — admin@nepmart.test   / admin123');
$out('  Seller    — himgadgets@nepmart.test / seller123');
$out('  Seller    — apparel@nepmart.test / seller123');
$out('  Customer  — buyer@nepmart.test   / customer123');
