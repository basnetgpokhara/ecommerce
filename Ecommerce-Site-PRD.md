# Product Requirements Document (PRD)
## Multi-Vendor E-Commerce Platform

**Version:** 1.0
**Date:** August 16, 2026
**Document Owner:** Product Team
**Status:** Draft

---

## 1. Overview

### 1.1 Purpose
This document defines the requirements for a modern, fully responsive, multi-vendor e-commerce web platform built on native PHP (custom lightweight MVC), MySQL/MariaDB, and MDBootstrap (CDN) for the front-end presentation layer. The platform allows customers to browse and purchase physical products, sellers to manage their own product catalog, and administrators to govern the entire marketplace.

### 1.2 Background
The business needs a self-hosted, cost-effective e-commerce solution (no reliance on SaaS platforms like Shopify) that supports local payment gateways (eSewa, Khalti, Fonepay/PhonePay) commonly used in Nepal, with full control over source code, data, and hosting.

### 1.3 Objectives
- Launch a visually modern, fully responsive storefront that works on desktop, tablet, and mobile.
- Allow guest browsing (no login required to view products/categories/shops).
- Require login for checkout/purchase.
- Support three distinct roles: **Customer**, **Seller**, **Administrator** — each with a dedicated dashboard.
- Enable sellers to independently manage (create/edit/delete) their own product listings.
- Give admins full oversight and control of users, sellers, products, orders, and payments.
- Integrate local digital wallets/payment gateways: eSewa, Khalti, Fonepay (PhonePay).
- Generate printable/PDF invoices and order receipts via a print-friendly A4 HTML view.
- Enforce strong security: hashed passwords, CSRF protection, session-based RBAC, prepared statements (SQL-injection safe).

### 1.4 Out of Scope (Phase 1)
- Mobile native apps (iOS/Android).
- Digital/downloadable products.
- Multi-currency / multi-language support (can be a future phase).
- Real-time chat support (future phase — placeholder only).

---

## 2. Tech Stack

| Layer | Technology |
|---|---|
| Frontend | PHP server-rendered views + MDBootstrap (via CDN), vanilla JS/jQuery for interactivity |
| Backend | Native PHP, custom lightweight MVC framework (no heavy frameworks like Laravel) |
| Database | MySQL / MariaDB, accessed via PDO with prepared statements only |
| Auth & Security | PHP native sessions, Role-Based Access Control (RBAC), `password_hash()`/`password_verify()`, CSRF tokens on all state-changing forms |
| PDF / Invoice | Print-friendly A4 HTML invoice template → browser "Print to PDF" (no external PDF library dependency in Phase 1) |
| Payments | eSewa, Khalti, Fonepay (PhonePay) — server-to-server verification via each gateway's API |
| Hosting/Infra | LAMP/LEMP stack compatible (Apache/Nginx + PHP-FPM + MySQL) |

### 2.1 Architecture Notes
- **Custom MVC structure:** `/app/controllers`, `/app/models`, `/app/views`, `/public` (single entry `index.php` front controller), `/config`, `/core` (Router, DB, Session, Auth, CSRF helper classes).
- **No ORM** — raw SQL via PDO prepared statements wrapped in a lightweight Database/QueryBuilder helper class.
- **Sessions** store `user_id`, `role`, and a CSRF token seed; role checked via middleware-style guard functions (`requireRole('admin')`, `requireAuth()`) at the top of protected controllers.
- **Separate route namespaces** for public site, customer dashboard, seller dashboard, and admin dashboard (e.g. `/`, `/account/*`, `/seller/*`, `/admin/*`).

---

## 3. User Roles & Permissions

| Capability | Guest | Customer | Seller | Admin |
|---|:---:|:---:|:---:|:---:|
| Browse products/categories/shops | ✅ | ✅ | ✅ | ✅ |
| Search & filter products | ✅ | ✅ | ✅ | ✅ |
| Add to cart / wishlist | ❌ (prompt to login) | ✅ | ✅ (as buyer) | ✅ |
| Checkout & pay | ❌ | ✅ | ✅ | ✅ |
| View own order history / track orders | ❌ | ✅ | ✅ | ✅ |
| Manage own profile & addresses | ❌ | ✅ | ✅ | ✅ |
| Add/Edit/Delete own products | ❌ | ❌ | ✅ | ✅ (any product) |
| View own sales & earnings | ❌ | ❌ | ✅ | ✅ (all sellers) |
| Manage own shop profile/banner | ❌ | ❌ | ✅ | ✅ |
| Approve/reject seller registrations | ❌ | ❌ | ❌ | ✅ |
| Approve/reject product listings | ❌ | ❌ | ❌ | ✅ |
| Manage categories | ❌ | ❌ | ❌ | ✅ |
| Manage all users (block/unblock) | ❌ | ❌ | ❌ | ✅ |
| Manage all orders & refunds | ❌ | ❌ | ❌ | ✅ |
| Configure payment gateway settings | ❌ | ❌ | ❌ | ✅ |
| View site-wide analytics/reports | ❌ | ❌ | ❌ | ✅ |
| Manage banners/homepage content | ❌ | ❌ | ❌ | ✅ |

---

## 4. Public Storefront (Frontend) Requirements

### 4.1 Global Layout
- **Top Bar:** contact info, currency/help links, login/register or account dropdown (shows "My Account", "My Orders", "Logout" when logged in), cart icon with live item count.
- **Main Navbar:** logo, mega-menu style category dropdown, search bar with live/autosuggest, cart icon, wishlist icon, sticky-on-scroll behavior.
- **Footer:** about the company, quick links, customer service links (returns, shipping policy, FAQs), payment method icons (eSewa/Khalti/Fonepay/Cards), social media links, newsletter signup, copyright.
- Fully responsive using MDBootstrap grid — mobile hamburger menu with collapsible category accordion.

### 4.2 Homepage Sections
1. **Hero Banner:** full-width auto-sliding carousel (admin-manageable slides: image, headline, CTA button, link).
2. **Category Section:** grid/carousel of shop-by-category cards with icons/images, linking to category listing pages.
3. **Featured/Trending Products Section:** card grid with product image, name, price, discount badge, rating, quick "Add to Cart" and "Wishlist" buttons; tabs for "New Arrivals / Best Sellers / Deals."
4. **Our Shops / Sellers Section:** showcase of active seller shops (logo, shop name, rating, "Visit Shop" button).
5. **Promotional Banner Strip:** secondary banner (e.g., seasonal sale, free shipping notice).
6. **Testimonials / Newsletter signup** (optional enhancement section).

### 4.3 Product Listing & Detail Pages
- Category/shop listing pages with filters (price range, brand, rating, availability) and sorting (price, popularity, newest).
- Pagination or infinite scroll.
- Product detail page: image gallery with zoom, price, stock status, quantity selector, Add to Cart / Buy Now, product description tabs (details, specifications, reviews/ratings), related products carousel, seller info card.

### 4.4 Cart & Checkout (Login Required)
- Cart page: editable quantities, remove item, price summary, coupon/promo code field.
- Guest attempting checkout is redirected to Login/Register (with return-to-cart flow).
- Checkout page: shipping address selection/entry, order summary, payment method selection (eSewa / Khalti / Fonepay / Cash on Delivery — configurable by admin), place order.
- Order confirmation page + emailed/dashboard invoice.

### 4.5 Authentication Pages
- Separate, clearly distinguished Login/Register flows for **Customer** and **Seller** (e.g., "Register as Seller" link on customer registration page, or a role toggle).
- Fields: name, email, phone, password (with strength meter), confirm password.
- Email verification / OTP (optional Phase 1.1 enhancement).
- Forgot password / reset password via emailed token link.
- Admin accounts are not self-registerable — created via seeded/internal admin panel only.

---

## 5. Customer Dashboard

- **Dashboard Home:** order summary widgets (pending, delivered, cancelled), recent activity.
- **My Orders:** list with status tracking (Placed → Confirmed → Shipped → Delivered / Cancelled), order detail view, downloadable/printable invoice (A4 print view).
- **My Profile:** edit name, email, phone, password change.
- **My Addresses:** add/edit/delete multiple shipping addresses, set default.
- **Wishlist:** saved products, move to cart.
- **My Reviews:** products reviewed, edit/delete own reviews.
- **Payment History:** list of transactions with gateway reference IDs and status.

---

## 6. Seller Dashboard

- **Dashboard Home:** sales overview (total sales, pending orders, revenue chart), low-stock alerts.
- **Shop Profile:** shop name, logo, banner image, description, contact info (subject to admin approval on changes).
- **Product Management:**
  - Add new product (name, category, images (multiple), price, discount price, stock quantity, SKU, description, specifications).
  - Edit own product.
  - Delete own product (soft delete recommended — flagged inactive rather than hard-deleted if orders reference it).
  - View product approval status (Pending / Approved / Rejected by Admin) — configurable whether admin pre-approval is required.
- **Order Management:** view orders containing their products only, update fulfillment status (Processing → Shipped), print packing slip/invoice.
- **Earnings & Payouts:** sales report, commission deducted (if applicable), payout request/history.
- **Reviews:** view customer reviews on their products.

---

## 7. Admin Dashboard

- **Dashboard Home:** site-wide KPIs — total sales, total orders, total users, total sellers, top-selling products, revenue chart, recent activity log.
- **User Management:** view/search/block/unblock/delete customers.
- **Seller Management:** approve/reject seller applications, view/edit/suspend seller shops, view seller sales performance.
- **Product Management:** view all products across sellers, approve/reject/edit/delete any listing, feature products on homepage.
- **Category Management:** create/edit/delete/reorder categories and subcategories, category images/icons.
- **Order Management:** view all orders, update statuses, process refunds/cancellations, view payment gateway transaction logs.
- **Payment Gateway Settings:** enable/disable and configure API keys/secrets for eSewa, Khalti, Fonepay, and Cash on Delivery.
- **Banner/CMS Management:** manage homepage hero banners, promotional strips, footer links, static pages (About Us, Privacy Policy, Terms, Return Policy).
- **Coupons & Discounts:** create/manage promo codes (percentage/flat, expiry, usage limits).
- **Reports & Analytics:** sales reports (daily/weekly/monthly), export to CSV, top products/sellers/customers.
- **Roles & Permissions:** manage admin sub-roles if needed (e.g., support staff with limited access) — optional Phase 2.
- **Activity/Audit Log:** track critical actions (logins, deletions, status changes) for accountability.

---

## 8. Payment Gateway Integration

| Gateway | Integration Notes |
|---|---|
| **eSewa** | Server-side form POST + signature verification via eSewa's payment API; success/failure callback URLs handled by dedicated controller; transaction verified via eSewa's status-check API before marking order as paid. |
| **Khalti** | Khalti Checkout (ePayment API) — initiate payment, redirect/verify via Khalti's lookup API using the returned `pidx`/token; webhook or callback verification required before order confirmation. |
| **Fonepay (PhonePay)** | QR/merchant redirect flow per Fonepay's merchant API; verify transaction status via Fonepay's verification endpoint. |
| **Cash on Delivery (COD)** | Optional fallback method, admin togglable per region. |

**Common Requirements:**
- All payment credentials (merchant codes, secret keys) stored securely in a non-public `.env`/config file, never hard-coded or exposed client-side.
- Every transaction is logged in a `payments` table with gateway reference ID, amount, status (Initiated / Success / Failed / Refunded), and timestamp.
- Orders move to "Paid/Confirmed" status **only** after server-side verification callback — never trust client-side redirect alone.
- Failed/abandoned payments allow retry without duplicate order creation.

---

## 9. Security Requirements

- **Password Security:** all passwords hashed with `password_hash()` (bcrypt/Argon2); never stored in plain text.
- **CSRF Protection:** unique CSRF token generated per session and validated on every POST/PUT/DELETE form submission.
- **SQL Injection Prevention:** 100% PDO prepared statements; no raw string-concatenated queries.
- **RBAC:** every controller/action checks the logged-in user's role before executing; unauthorized access redirects to login or shows a 403 page.
- **Session Security:** session regenerate on login (session fixation prevention), secure & HttpOnly cookie flags, session timeout after inactivity.
- **Input Validation & Sanitization:** server-side validation on all forms (required fields, type/format checks); output escaped (`htmlspecialchars`) to prevent XSS.
- **File Upload Security:** product images restricted by MIME type/extension whitelist, size limits, renamed on upload, stored outside web-executable paths where feasible.
- **Rate Limiting/Brute-force Protection:** login attempt throttling/lockout after repeated failures.
- **HTTPS Enforced:** all pages, especially checkout/payment/auth, served over SSL/TLS.

---

## 10. Database Schema (High-Level)

Core tables (illustrative, not exhaustive):
- `users` (id, name, email, phone, password_hash, role [customer/seller/admin], status, created_at)
- `sellers` (user_id FK, shop_name, shop_logo, shop_banner, description, status, commission_rate)
- `categories` (id, name, slug, parent_id, image, sort_order)
- `products` (id, seller_id FK, category_id FK, name, slug, description, price, discount_price, stock, sku, status [pending/approved/rejected], created_at)
- `product_images` (id, product_id FK, image_path, is_primary)
- `orders` (id, customer_id FK, address_id FK, total_amount, status, payment_method, payment_status, created_at)
- `order_items` (id, order_id FK, product_id FK, seller_id FK, quantity, unit_price, subtotal)
- `payments` (id, order_id FK, gateway, transaction_ref, amount, status, raw_response, created_at)
- `addresses` (id, user_id FK, label, full_address, city, phone, is_default)
- `cart` / `cart_items` (session or user based)
- `wishlist`
- `reviews` (id, product_id FK, customer_id FK, rating, comment, status)
- `coupons` (id, code, type, value, expiry, usage_limit)
- `banners` (id, image, title, link, sort_order, status)
- `audit_logs` (id, user_id FK, action, description, created_at)

---

## 11. Invoice / PDF Requirements

- A print-friendly, A4-sized HTML invoice/order-receipt template (dedicated CSS `@media print` styles, no external PDF library dependency in Phase 1).
- Includes: company logo/header, invoice number, order date, customer & shipping details, itemized product list with quantity/price/subtotal, tax (if applicable), total, payment method & status.
- Accessible from both Customer "My Orders" and Seller "Order Management" (seller sees only their line items) and Admin order detail view.
- Triggered via a "Print / Download PDF" button that opens the browser's native print dialog (Save as PDF).

---

## 12. Non-Functional Requirements

- **Responsiveness:** fully responsive across mobile (≥320px), tablet, and desktop breakpoints using MDBootstrap's grid system.
- **Performance:** homepage and category pages should load within 2–3 seconds on standard broadband; images optimized/lazy-loaded.
- **Browser Compatibility:** latest Chrome, Firefox, Edge, Safari.
- **SEO-Friendly:** clean URL slugs for products/categories, meta title/description fields editable by admin/seller.
- **Accessibility:** semantic HTML, sufficient color contrast, alt text on images.
- **Scalability:** modular MVC structure to allow future addition of features (multi-language, mobile app API, etc.) without major rewrites.
- **Maintainability:** consistent coding standards, config-driven settings (site name, logo, currency, gateway keys) rather than hard-coded values.

---

## 13. Success Metrics (KPIs)

- Number of registered customers and active sellers within first 3 months.
- Conversion rate (visits → completed orders).
- Average order value (AOV).
- Cart abandonment rate.
- Successful payment transaction rate per gateway.
- Seller product approval turnaround time.
- Page load speed / Core Web Vitals.

---

## 14. Suggested Development Phases

| Phase | Scope |
|---|---|
| **Phase 1** | Core MVC setup, DB schema, auth (3 roles) + RBAC, public storefront (homepage, category, product, shop pages), cart, basic checkout with COD only. |
| **Phase 2** | Seller dashboard (product CRUD, orders), Admin dashboard (user/seller/product/category management), reviews & ratings. |
| **Phase 3** | Payment gateway integrations (eSewa, Khalti, Fonepay), invoice/PDF generation, coupons/discounts. |
| **Phase 4** | Admin analytics & reports, banner/CMS management, audit logs, performance optimization, security hardening/pen-testing. |
| **Phase 5 (Future)** | Multi-language, native mobile apps, live chat support, advanced recommendation engine. |

---

## 15. Open Questions / Assumptions

- Is a per-seller commission model required, or is each seller's revenue tracked purely for reporting?
- Should product listings require admin approval before going live, or publish immediately with post-hoc moderation?
- Is email/SMS/OTP verification required at registration, or is it deferred to a later phase?
- Should the platform support only Nepal-based shipping/addresses initially, or plan for broader geography?
- Confirm exact gateway: "PhonePay" — assumed to mean **Fonepay** (Nepali gateway) rather than India's PhonePe; please confirm.

---

*End of Document*
