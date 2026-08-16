# NepMart — Multi-Vendor E-Commerce Platform

A modern, fully responsive **multi-vendor marketplace** built on **native PHP (custom lightweight MVC)**, **MySQL/MariaDB (PDO + prepared statements)**, and **MDBootstrap (CDN)**. Customers browse and buy; sellers run their own shops; admins govern the marketplace.

This repository implements **Phase 1** of the PRD: the custom MVC framework, full database schema, 3-role authentication with RBAC, the public storefront (home, category, product, shop pages), cart, and checkout with **Cash on Delivery** (COD). The schema and hooks are laid out so Phases 2–4 drop in cleanly.

> Built against the *Ecommerce-Site-PRD.md* (v1.0). Phase-1 product-approval mode is **configurable (defaults to auto-publish)**; seller revenue uses a **per-seller commission** model.

---

## ✨ Features (Phase 1)

**Framework & security**
- Custom MVC: `Router`, `Database` (PDO singleton), `Session`, `Auth` (RBAC guards), `Csrf`, `Request`, `View`, base `Controller`/`Model`
- 100% PDO **prepared statements** (no string-concatenated SQL)
- `password_hash` / `password_verify` (bcrypt), **CSRF** on every state-changing form, **session regeneration** on login, `HttpOnly`+`SameSite` cookies, brute-force login throttling
- PSR-4-style autoloader, `.env` config, config-driven `settings` table
- Print-friendly **A4 invoice** (browser “Save as PDF”, `@media print`)

**Storefront (guest-browseable)**
- Homepage: hero carousel, shop-by-category grid, featured/new-arrivals/deals tabs, promo strip, shops showcase
- Category listing with **price/brand filters**, sorting, pagination
- Product detail: image gallery, quantity selector, Add-to-Cart / Buy Now, description/specs/reviews tabs, related products, seller card
- Shop directory + single-shop pages
- Live search, sticky navbar, mega-menu, mobile hamburger

**Auth & accounts**
- Login / Register (Customer **and** Seller flows), password reset via token, logout
- Customer dashboard: order widgets, order history + detail + printable invoice, profile & password change, address book
- Role-gated **Seller** and **Admin** dashboards (overview KPIs; full management arrives in Phase 2)

**Commerce**
- Login-required cart + checkout, address selection/creation, COD order placement, stock decrement, transactional order creation, payment logging, order confirmation + invoice

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
│   ├── controllers/            # Home, Product, Category, Shop, Cart,
│   │                           #   Checkout, Auth, Account, Seller, Admin, Page
│   ├── models/                 # User, Seller, Category, Product, ProductImage,
│   │                           #   Cart, Address, Order, OrderItem, Payment,
│   │                           #   Review, Coupon, Banner, Page, Setting, AuditLog
│   └── views/                  # layouts/ · partials/ · home/ category/ product/
│                               #   shops/ cart/ checkout/ auth/ account/ seller/
│                               #   admin/ pages/ errors/
├── core/                       # Router, Database, Session, Auth, Csrf, Request,
│                               #   View, Controller, Model, helpers.php
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
- Apache with `mod_rewrite` **or** Nginx (or just PHP’s built-in server for dev)

---

## 🚀 Quick start (recommended — phpMyAdmin, no Composer/CLI)

1. **Copy the project** into your web root, e.g. `htdocs/nepmart` (XAMPP/WAMP) or `/var/www/nepmart` (LAMP).
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

> The app **auto-detects its sub-folder**, so `localhost/nepmart`, `localhost/nepmart/public`, a domain root, or a sub-domain all work without editing `APP_URL`. Set `APP_URL` in `.env` only to force a specific URL (production/reverse-proxy).

---

## 🛠️ Alternative: command-line setup

```bash
# 1. Get the code
git clone <repo-url> nepmart && cd nepmart

# 2. Configure environment
cp .env.example .env        # → set DB_HOST/DB_NAME/DB_USER/DB_PASS

# 3. Create database + import (schema + demo data in one file)
mysql -u root -p -e "CREATE DATABASE nepmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p nepmart < database/nepmart.sql

#    (optional) or import schema then run the PHP seeder instead:
#    mysql -u root -p nepmart < database/schema.sql && php database/seed.php

# 4. Make uploads writable
chmod -R 775 public/uploads
```

### Run locally (PHP built-in server)

```bash
php -S 0.0.0.0:8000 -t public public/index.php
# open http://localhost:8000   (APP_URL auto-detected)
```

### Production (Apache)
Point a virtual host at the project root. The root `.htaccess` rewrites everything into `/public`, and `public/.htaccess` routes non-file requests to `index.php`. Set `APP_URL`, `APP_DEBUG=false`, and serve over HTTPS.

### Production (Nginx)
```nginx
root /var/www/ecommerce/public;
index index.php;
location / { try_files $uri $uri/ /index.php?$query_string; }
location ~ \.php$ { include fastcgi_params; fastcgi_pass unix:/run/php/php8.2-fpm.sock;
                    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; }
```

---

## 🔑 Demo accounts (created by the seeder)

| Role     | Email                    | Password      |
|----------|--------------------------|---------------|
| Admin    | admin@nepmart.test       | `admin123`    |
| Seller   | himgadgets@nepmart.test  | `seller123`   |
| Seller   | apparel@nepmart.test     | `seller123`   |
| Seller   | everest@nepmart.test     | `seller123`   |
| Customer | buyer@nepmart.test       | `customer123` |

> **Change these immediately** on any real deployment. Admins are never self-registerable — create them via the seeder / a controlled process.

---

## ⚙️ Configuration

Runtime settings live in the `settings` table (seeded defaults shown):

| Key | Default | Purpose |
|-----|---------|---------|
| `site_name`, `site_tagline`, `contact_email`, `contact_phone` | — | Branding & contact |
| `currency_code` / `currency_symbol` | `NPR` / `रू` | Storefront currency |
| `approval_mode` | `auto` | `auto` = products go live immediately (post-moderation); `pending` = require admin approval first |
| `default_commission_rate` | `10.00` | Applied to new sellers |
| `cod_enabled` | `1` | Cash on Delivery |
| `esewa_enabled` / `khalti_enabled` / `fonepay_enabled` | `0` | Payment gateways (Phase 3) |
| `free_shipping_threshold` / `shipping_fee` | `2000` / `150` | Checkout shipping logic |

Environment values (`.env`): `APP_NAME`, `APP_ENV`, `APP_DEBUG`, `APP_URL`, `DB_*`, `SESSION_*`, `CSRF_TOKEN_NAME`.

---

## 🛣️ Route map (Phase 1)

```
Public:     GET  /  /search  /categories  /category/{slug}  /product/{slug}
            GET  /shops  /shop/{slug}  /page/{slug}
Auth:       GET/POST  /login  /register  /forgot  /reset        GET  /logout
Cart*:      GET  /cart   POST  /cart/add  /cart/update  /cart/remove  /cart/clear
Checkout*:  GET  /checkout   POST  /checkout/place
            GET  /checkout/success/{id}   /order/{id}/invoice
Account*:   /account  /account/orders[/{id}]  /account/profile
            /account/password  /account/addresses  (+ POST/PUT/DELETE)
Seller*:    GET  /seller      (role: seller)
Admin*:     GET  /admin       (role: admin)
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
- ⏳ **Enforce HTTPS** at the web-server layer in production (recommended)

---

## 🗺️ Roadmap (PRD phases)

- **Phase 1 ✅** — MVC, schema, 3-role auth + RBAC, storefront, cart, COD checkout
- **Phase 2** — Seller product CRUD + shop profile, Admin management (users/sellers/products/categories/orders), reviews submission/moderation
- **Phase 3** — eSewa, Khalti, Fonepay server-to-server verification; coupons/discounts
- **Phase 4** — Admin analytics/reports, banner/CMS management, audit log UI, security hardening
- **Phase 5 (future)** — Multi-language, native mobile apps, live chat, recommendations

The database schema already includes `coupons`, `banners`, `pages`, `audit_logs`, gateway columns in `payments`, and commission accounting in `order_items`, so later phases extend rather than rework.

---

## 📝 Notes

- **No Composer dependency** in Phase 1 — pure native PHP.
- Images are lightweight inline **SVG** (brand mark, category icons, product art, hero/shop banners) under `public/assets/img` — no binary assets to manage and they scale crisply.
- MDBootstrap is loaded from a CDN; if you need a fully offline/air-gapped install, vendor the MDB CSS/JS locally and update the `<link>`/`<script>` tags in the layouts.
- File uploads (product/shop images) go to `public/uploads/`, which is git-ignored except for `.gitkeep`.

## License

Proprietary — © NepMart. All rights reserved.
