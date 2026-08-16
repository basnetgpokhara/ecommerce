# NepMart — Multi-Vendor E-Commerce Platform

A modern, fully responsive **multi-vendor marketplace** built on **native PHP (custom lightweight MVC)**, **MySQL/MariaDB (PDO + prepared statements)**, and **MDBootstrap (CDN)**. Customers browse and buy; sellers run their own shops; admins govern the marketplace.

This repository implements **Phases 1–4** of the PRD: storefront/auth/checkout (1), seller & admin tools + reviews (2), online payments & coupons (3), and analytics + performance hardening (4).

> Built against the *Ecommerce-Site-PRD.md* (v1.0). Product approval is **configurable (defaults to auto-publish)**; seller revenue uses a **per-seller commission** model.

---

## ✨ Features

### Phase 1 — Storefront, auth, cart, checkout
- **Custom MVC** (`core/`): Router, Database (PDO singleton), Session, Auth (RBAC guards), Csrf, Request, View, base Controller/Model — no Composer
- **Security**: 100% prepared statements, `password_hash`/verify, CSRF on all state-changing forms, session regeneration, HttpOnly+SameSite cookies, brute-force throttling, `htmlspecialchars` escaping
- **Public storefront**: homepage (hero carousel, categories, featured/new/deals tabs, shops), category listing (price/brand filters, sort, pagination), product detail (gallery, qty, Add-to-Cart/Buy Now, tabs, related), shop directory, search, mega-menu, fully responsive
- **Auth**: login/register (Customer **and** Seller), password reset, logout
- **Cart + COD checkout** (login-required): address selection/creation, transactional orders, stock decrement, payment logging, **print-friendly A4 invoice**
- **Customer dashboard**: order widgets, history + detail + invoice, profile, password change, address book

### Phase 2 — Seller tools, admin management, reviews
- **Seller dashboard**
  - Product **CRUD** with multiple-image upload (MIME/size-validated), category/brand/SKU, pricing, discount, stock; **approval status** respects the configured mode
  - **Orders**: view orders containing the seller's products, update **fulfillment** (processing → shipped → delivered)
  - **Shop profile**: name, logo, banner, description, contact info
  - **Reviews**: see all reviews on the seller's products
- **Admin dashboard**
  - **Users**: search/filter, block/unblock, delete (self-protected)
  - **Sellers**: approve pending applications, suspend/activate, edit commission/status/contact
  - **Products**: approve/reject, feature/unfeature, delete (filter by status/shop)
  - **Categories**: create/edit/delete (parent–child, slug-safe, delete-guarded)
  - **Orders**: view all, update status, invoices, payment log
  - **Reviews**: moderate (approve/reject)
  - **Banners & CMS pages**: full management
  - **Settings**: payment-gateway toggles + keys (eSewa/Khalti/Fonepay/COD), commission, approval mode, shipping, currency, site info
  - **Audit log**: every critical action is recorded
- **Reviews & ratings**: customers submit one review per product (auto-approved by default, or admin-moderated); aggregates shown on product cards/detail

### Phase 3 — Online payments (server-to-server) & coupons
- **eSewa, Khalti, Fonepay** — full redirect/JS-widget flows with **server-to-server verification** (never trust the callback alone):
  - eSewa: signed (`sct` HMAC) redirect → verify via `epay/transrec` XML
  - Khalti: Khalti Checkout JS widget → verify token via the Khalti Verify API (`Authorization: Key`)
  - Fonepay: merchant-request redirect (`DV` HMAC) → verify via the merchant verification API
  - Config per gateway (merchant id/code, secret, public key, **test/live environment**) from **Admin → Settings**; test endpoints default so you can validate without live keys
  - Order stays `placed`/`pending` until the gateway confirms; confirmed orders flip to `paid`/`confirmed` and every attempt is logged in `payments` + audit
  - Payment-return/callback routes are **CSRF-exempt** but verified by the gateway signature — this is intentional and documented
- **Coupons & discounts** — admin-managed coupons (percentage/flat, min order, expiry, usage limit, active/inactive), apply/remove from the cart, reflected in checkout totals and stored per order; usage is counted and surfaced in analytics

### Phase 4 — Analytics & performance
- **Admin analytics/reports** (`/admin/analytics`): revenue & order trends over selectable ranges (7–90 days), KPI cards, order-status and payment-method breakdowns, top sellers/products, coupon usage — charted with Chart.js
- **Performance hardening**: additional indexes on high-traffic tables (`orders.created_at`, `orders.payment_method`, `payments.status`, `reviews.status`, `order_items.product_id`), pagination on every admin list (users, sellers, products, orders, reviews, coupons), settings cached per request, and bounded queries throughout

---

## 🧱 Tech stack

| Layer        | Technology |
|--------------|------------|
| Frontend     | PHP server-rendered views + MDBootstrap (CDN), vanilla JS |
| Backend      | Native PHP 8.1+, custom lightweight MVC (no heavy framework) |
| Database     | MySQL / MariaDB via PDO prepared statements |
| Auth/Security| PHP sessions, RBAC, `password_hash`, CSRF tokens |
| Invoice/PDF  | Print-friendly A4 HTML → browser “Print to PDF” |
| Payments     | COD (Phase 1) · eSewa, Khalti, Fonepay (Phase 3 hooks ready) |
| Hosting      | LAMP/LEMP compatible (Apache/Nginx + PHP-FPM + MySQL) |

---

## 📁 Project structure

```
ecommerce/
├── public/                     # web root (single entry point)
│   ├── index.php               # front controller (+ php -S router)
│   ├── .htaccess               # Apache rewrite
│   └── assets/                 # css, js, img (SVG brand/product/shop art)
├── app/
│   ├── controllers/            # Home, Product, Category, Shop, Cart, Checkout,
│   │                           #   Auth, Account, Seller (CRUD), Admin (mgmt), Page
│   ├── models/                 # User, Seller, Category, Product, ProductImage,
│   │                           #   Cart, Address, Order, OrderItem, Payment,
│   │                           #   Review, Coupon, Banner, Page, Setting, AuditLog
│   └── views/                  # layouts/ · partials/ · home/ category/ product/
│                               #   shops/ cart/ checkout/ auth/ account/ seller/
│                               #   admin/ pages/ errors/
├── core/                       # Router, Database, Session, Auth, Csrf, Request,
│                               #   View, Upload, Controller, Model, helpers.php
├── config/                     # config.php (bootstrap + .env + autoloader)
├── database/
│   ├── nepmart.sql           # ★ ONE-FILE import: schema + demo data + logins (phpMyAdmin)
│   ├── schema.sql            # schema DDL only
│   └── seed.php              # CLI demo-data seeder (hashed passwords)
├── routes.php                  # central route table
├── .env.example
├── .htaccess                   # routes domain root → /public
└── README.md
```

---

## ✅ Requirements

- **PHP 8.1+** with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` (standard in most installs)
- **MySQL 5.7+ / MariaDB 10.3+**
- Apache with `mod_rewrite` **or** Nginx (or PHP’s built-in server for dev)

---

## 🚀 Quick start (recommended — phpMyAdmin, no Composer/CLI)

1. **Copy the project** into your web root, e.g. `htdocs/nepmart` (XAMPP/WAMP).
2. **Create a database** in phpMyAdmin (e.g. `nepmart`, collation `utf8mb4_unicode_ci`) and select it.
3. **Import `database/nepmart.sql`** via *Import → Choose File → Go*. This single file creates **all tables + demo data + working logins** (no `seed.php` needed).
4. **Copy `.env.example` to `.env`** and set only the database credentials:
   ```env
   DB_NAME=nepmart
   DB_USER=root
   DB_PASS=        # your MySQL password
   ```
   (`APP_URL` is optional — the app auto-detects it.)
5. **Open it:** `http://localhost/nepmart` — done. 🎉

> The app **auto-detects its sub-folder**, so `localhost/nepmart`, `localhost/nepmart/public`, a domain root, or a sub-domain all work without editing `APP_URL`.

---

## 🛠️ Alternative: command-line setup

```bash
git clone <repo-url> nepmart && cd nepmart
cp .env.example .env        # → set DB_HOST/DB_NAME/DB_USER/DB_PASS
mysql -u root -p -e "CREATE DATABASE nepmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p nepmart < database/nepmart.sql
chmod -R 775 public/uploads
```

---

### Run locally (PHP built-in server)

```bash
php -S 0.0.0.0:8000 -t public public/index.php
# open http://localhost:8000   (APP_URL auto-detected)
```

### Production (Apache)
Point a virtual host at the project root. The root `.htaccess` rewrites everything into `/public`, and `public/.htaccess` routes non-file requests to `index.php`. Set `APP_URL`, `APP_DEBUG=false`, serve over HTTPS.

### Production (Nginx)
```nginx
root /var/www/ecommerce/public;
index index.php;
location / { try_files $uri $uri/ /index.php?$query_string; }
location ~ \.php$ { include fastcgi_params; fastcgi_pass unix:/run/php/php8.2-fpm.sock;
                    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; }
```

---

## 🔑 Demo accounts (created by the seeder / nepmart.sql)

| Role     | Email                    | Password      |
|----------|--------------------------|---------------|
| Admin    | admin@nepmart.test       | `admin123`    |
| Seller   | himgadgets@nepmart.test  | `seller123`   |
| Seller   | apparel@nepmart.test     | `seller123`   |
| Customer | buyer@nepmart.test       | `customer123` |

**Demo coupons** (seeded): `WELCOME10` (10% off, min रू 1,000) and `SAVE200` (flat रू 200 off, min रू 1,500). Apply one in the cart.

> **Change these immediately** on any real deployment. Admins are never self-registerable — create them via the seeder / a controlled process.

---

## ⚙️ Configuration

Runtime settings live in the `settings` table (editable from **Admin → Settings**):

| Key | Default | Purpose |
|-----|---------|---------|
| `site_name`, `site_tagline`, `contact_email`, `contact_phone` | — | Branding & contact |
| `currency_code` / `currency_symbol` | `NPR` / `रू` | Storefront currency |
| `approval_mode` | `auto` | `auto` = products go live immediately; `pending` = require admin approval |
| `default_commission_rate` | `10.00` | Applied to new sellers (editable per seller) |
| `cod_enabled` | `1` | Cash on Delivery |
| `esewa_enabled` / `khalti_enabled` / `fonepay_enabled` | `0` | Enable each online gateway |
| `esewa_merchant_id` / `esewa_secret` / `esewa_environment` | — / — / `test` | eSewa credentials + mode |
| `khalti_public_key` / `khalti_secret_key` / `khalti_environment` | — / — / `test` | Khalti credentials + mode |
| `fonepay_merchant_code` / `fonepay_secret` / `fonepay_environment` | — / — / `test` | Fonepay credentials + mode |
| `free_shipping_threshold` / `shipping_fee` | `2000` / `150` | Checkout shipping logic |
| `review_auto_approve` | `1` | Auto-approve customer reviews (else admin moderates) |

Environment values (`.env`): `APP_NAME`, `APP_ENV`, `APP_DEBUG`, `APP_URL` (optional), `DB_*`, `SESSION_*`, `CSRF_TOKEN_NAME`.

---

## 🛣️ Route map

```
Public:     GET  /  /search  /categories  /category/{slug}  /product/{slug}
            GET  /shops  /shop/{slug}  /page/{slug}
            POST /product/{slug}/review           (customer)
Auth:       GET/POST  /login  /register  /forgot  /reset   ·   GET /logout
Cart*:      /cart   (+ add/update/remove/clear)
Checkout*:  /checkout  → /checkout/place  ·  /checkout/success/{id}  ·  /order/{id}/invoice
Account*:   /account  /account/orders[/{id}]  /account/profile  /account/password
            /account/addresses (+ CRUD)  ·  /account/reviews/{id}/delete
Seller*:    /seller  ·  /seller/products (/create · /{id}/edit · POST store/update/delete)
            /seller/orders (/{id} · /{id}/fulfill)  ·  /seller/reviews  ·  /seller/shop
Admin*:     /admin  ·  /admin/users  ·  /admin/sellers (/approve · /suspend · /{id}/edit)
            /admin/products (/approve · /reject · /feature · /delete)
            /admin/categories (+ create/update/delete)  ·  /admin/orders (/{id} · /{id}/status)
            /admin/reviews (/{id}/{status})  ·  /admin/banners  ·  /admin/pages
            /admin/settings  ·  /admin/audit
(* login / role required)
```

---

## 🔒 Security checklist

- ✅ All passwords hashed with `password_hash()` (never stored plaintext)
- ✅ CSRF token validated on every POST/PUT/DELETE
- ✅ Every query uses PDO prepared statements
- ✅ RBAC guards (`requireAuth`, `requireRole`) on protected controllers
- ✅ Session regeneration on login (fixation prevention), secure/HttpOnly/SameSite cookies
- ✅ Output escaped with `htmlspecialchars()` (`e()`) to prevent XSS
- ✅ Login brute-force throttling (lockout after repeated failures)
- ✅ Image uploads validated by extension + MIME, size-limited, randomized filenames
- ⏳ **Enforce HTTPS** at the web-server layer in production (recommended)

---

## 🗺️ Roadmap (PRD phases)

- **Phase 1 ✅** — MVC, schema, 3-role auth + RBAC, storefront, cart, COD checkout
- **Phase 2 ✅** — Seller product CRUD + shop profile + orders/fulfillment; Admin management (users, sellers, products, categories, orders, reviews, banners, pages, settings, audit); customer reviews & moderation
- **Phase 3 ✅** — eSewa, Khalti, Fonepay server-to-server verification; coupons/discounts
- **Phase 4 ✅** — Admin analytics/reports, performance hardening
- **Phase 5 (future)** — Multi-language, native mobile apps, live chat, recommendations

> **Testing payments:** enable a gateway under **Admin → Settings**, leave it in `test` mode, and place an order choosing that method. The flow works end-to-end against the sandbox once you supply the gateway's sandbox keys. Without keys the gateway stays "disabled" and COD is used.

---

## 📝 Notes

- **No Composer dependency** — pure native PHP.
- Images are lightweight inline **SVG** under `public/assets/img`; seller/admin uploads go to `public/uploads/` (git-ignored).
- MDBootstrap loads from a CDN; vendor the CSS/JS locally for air-gapped installs.
- Phases 1–4 were built without a PHP/MySQL runtime in the build sandbox; all PHP was structurally validated (balanced braces/parens, string/heredoc/comment handling, all view/method references resolve, forms non-nested) and the SQL was reviewed. A `php -l` sweep + `database/nepmart.sql` import test on your side is still recommended. Online gateways additionally require live/sandbox credentials from eSewa, Khalti, and Fonepay to be exercised end-to-end.

## License

Proprietary — © NepMart. All rights reserved.
