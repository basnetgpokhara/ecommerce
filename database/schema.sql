-- =====================================================================
--  NepMart — Multi-Vendor Marketplace
--  Database schema (MySQL / MariaDB)
--  Run:  mysql -u root -p ecommerce < database/schema.sql
--  Charset: utf8mb4   Engine: InnoDB
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS banners;
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS sellers;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS settings;

-- ---------------------------------------------------------------------
--  Users (customers, sellers, admins share one table; role-based)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name            VARCHAR(120) NOT NULL,
    email           VARCHAR(190) NOT NULL,
    phone           VARCHAR(30)  NULL,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('customer','seller','admin') NOT NULL DEFAULT 'customer',
    status          ENUM('active','blocked') NOT NULL DEFAULT 'active',
    reset_token     VARCHAR(64)  NULL,
    reset_expires   DATETIME     NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY ix_users_role (role),
    KEY ix_users_reset (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Sellers (one per user of role=seller)
-- ---------------------------------------------------------------------
CREATE TABLE sellers (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED NOT NULL,
    shop_name       VARCHAR(150) NOT NULL,
    slug            VARCHAR(160) NOT NULL,
    shop_logo       VARCHAR(255) NULL,
    shop_banner     VARCHAR(255) NULL,
    description     TEXT         NULL,
    contact_phone   VARCHAR(30)  NULL,
    contact_email   VARCHAR(190) NULL,
    commission_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    status          ENUM('active','pending','suspended') NOT NULL DEFAULT 'pending',
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sellers_user (user_id),
    UNIQUE KEY uq_sellers_slug (slug),
    CONSTRAINT fk_sellers_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Categories (self-referencing for subcategories)
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(150) NOT NULL,
    slug        VARCHAR(160) NOT NULL,
    parent_id   INT UNSIGNED NULL,
    image       VARCHAR(255) NULL,
    icon        VARCHAR(50)  NULL,
    sort_order  INT          NOT NULL DEFAULT 0,
    is_featured TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_slug (slug),
    KEY ix_categories_parent (parent_id),
    CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Products
-- ---------------------------------------------------------------------
CREATE TABLE products (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    seller_id         INT UNSIGNED NOT NULL,
    category_id       INT UNSIGNED NULL,
    name              VARCHAR(200) NOT NULL,
    slug              VARCHAR(220) NOT NULL,
    short_description VARCHAR(300) NULL,
    description       TEXT         NULL,
    specifications    TEXT         NULL,
    brand             VARCHAR(100) NULL,
    price             DECIMAL(12,2) NOT NULL,
    discount_price    DECIMAL(12,2) NULL,
    stock             INT          NOT NULL DEFAULT 0,
    sku               VARCHAR(80)  NULL,
    status            ENUM('pending','approved','rejected','inactive') NOT NULL DEFAULT 'approved',
    is_featured       TINYINT(1)   NOT NULL DEFAULT 0,
    meta_title        VARCHAR(200) NULL,
    meta_description  VARCHAR(300) NULL,
    rating_avg        DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    rating_count      INT          NOT NULL DEFAULT 0,
    deleted_at        DATETIME     NULL,
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_products_slug (slug),
    KEY ix_products_category (category_id),
    KEY ix_products_seller (seller_id),
    KEY ix_products_status (status),
    KEY ix_products_featured (is_featured),
    CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES sellers(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Product images
-- ---------------------------------------------------------------------
CREATE TABLE product_images (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id  INT UNSIGNED NOT NULL,
    image_path  VARCHAR(255) NOT NULL,
    is_primary  TINYINT(1)   NOT NULL DEFAULT 0,
    sort_order  INT          NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_pimages_product (product_id),
    CONSTRAINT fk_pimages_product FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Addresses (shipping)
-- ---------------------------------------------------------------------
CREATE TABLE addresses (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED NOT NULL,
    label         VARCHAR(60)  NULL,
    full_name     VARCHAR(150) NOT NULL,
    phone         VARCHAR(30)  NOT NULL,
    address_line1 VARCHAR(200) NOT NULL,
    address_line2 VARCHAR(200) NULL,
    city          VARCHAR(100) NOT NULL,
    district      VARCHAR(100) NULL,
    postal_code   VARCHAR(20)  NULL,
    is_default    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_addresses_user (user_id),
    CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Cart items (per logged-in user)
-- ---------------------------------------------------------------------
CREATE TABLE cart_items (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL,
    product_id  INT UNSIGNED NOT NULL,
    quantity    INT          NOT NULL DEFAULT 1,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cart_user_product (user_id, product_id),
    KEY ix_cart_user (user_id),
    CONSTRAINT fk_cart_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Orders  (a multi-vendor order; line items carry seller_id)
-- ---------------------------------------------------------------------
CREATE TABLE orders (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_number     VARCHAR(40)  NOT NULL,
    customer_id      INT UNSIGNED NOT NULL,
    shipping_name    VARCHAR(150) NOT NULL,
    shipping_phone   VARCHAR(30)  NOT NULL,
    shipping_address VARCHAR(300) NOT NULL,
    shipping_city    VARCHAR(100) NOT NULL,
    shipping_district VARCHAR(100) NULL,
    subtotal         DECIMAL(12,2) NOT NULL,
    shipping_fee     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax              DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total            DECIMAL(12,2) NOT NULL,
    status           ENUM('placed','confirmed','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'placed',
    payment_method   ENUM('cod','esewa','khalti','fonepay') NOT NULL DEFAULT 'cod',
    payment_status   ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    coupon_code      VARCHAR(60)  NULL,
    notes            VARCHAR(300) NULL,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_orders_number (order_number),
    KEY ix_orders_customer (customer_id),
    KEY ix_orders_status (status),
    KEY ix_orders_created (created_at),
    KEY ix_orders_payment (payment_method),
    CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES users(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Order items (snapshot + commission accounting per line)
-- ---------------------------------------------------------------------
CREATE TABLE order_items (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id          INT UNSIGNED NOT NULL,
    product_id        INT UNSIGNED NULL,
    seller_id         INT UNSIGNED NOT NULL,
    product_name      VARCHAR(200) NOT NULL,
    product_image     VARCHAR(255) NULL,
    quantity          INT          NOT NULL,
    unit_price        DECIMAL(12,2) NOT NULL,
    subtotal          DECIMAL(12,2) NOT NULL,
    commission_rate   DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    commission_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    seller_earnings   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    fulfillment       ENUM('processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'processing',
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_oitems_order (order_id),
    KEY ix_oitems_seller (seller_id),
    KEY ix_oitems_product (product_id),
    CONSTRAINT fk_oitems_order FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_oitems_product FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_oitems_seller FOREIGN KEY (seller_id) REFERENCES sellers(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Payments (gateway transactions)
-- ---------------------------------------------------------------------
CREATE TABLE payments (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id        INT UNSIGNED NOT NULL,
    gateway         ENUM('cod','esewa','khalti','fonepay') NOT NULL,
    transaction_ref VARCHAR(190) NULL,
    amount          DECIMAL(12,2) NOT NULL,
    status          ENUM('initiated','success','failed','refunded') NOT NULL DEFAULT 'initiated',
    raw_response    TEXT         NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_payments_order (order_id),
    KEY ix_payments_status (status),
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Reviews & ratings
-- ---------------------------------------------------------------------
CREATE TABLE reviews (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id  INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    rating      TINYINT      NOT NULL,
    comment     TEXT         NULL,
    status      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_review_product_customer (product_id, customer_id),
    KEY ix_reviews_product (product_id),
    KEY ix_reviews_status (status),
    CONSTRAINT fk_reviews_product  FOREIGN KEY (product_id)  REFERENCES products(id)  ON DELETE CASCADE,
    CONSTRAINT fk_reviews_customer FOREIGN KEY (customer_id) REFERENCES users(id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Coupons / discounts (Phase 3 groundwork)
-- ---------------------------------------------------------------------
CREATE TABLE coupons (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code         VARCHAR(60)  NOT NULL,
    type         ENUM('percentage','flat') NOT NULL DEFAULT 'percentage',
    value        DECIMAL(12,2) NOT NULL,
    min_order    DECIMAL(12,2) NULL,
    expiry       DATETIME     NULL,
    usage_limit  INT          NULL,
    used         INT          NOT NULL DEFAULT 0,
    status       ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_coupons_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Homepage banners (hero + promo strips)
-- ---------------------------------------------------------------------
CREATE TABLE banners (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title       VARCHAR(200) NULL,
    subtitle    VARCHAR(300) NULL,
    image       VARCHAR(255) NULL,
    link        VARCHAR(255) NULL,
    button_text VARCHAR(60)  NULL,
    position    ENUM('hero','promo') NOT NULL DEFAULT 'hero',
    sort_order  INT          NOT NULL DEFAULT 0,
    status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_banners_position (position, status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  CMS pages (About, Privacy, Terms, Returns ...)
-- ---------------------------------------------------------------------
CREATE TABLE pages (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title       VARCHAR(200) NOT NULL,
    slug        VARCHAR(200) NOT NULL,
    body        LONGTEXT     NULL,
    status      ENUM('published','draft') NOT NULL DEFAULT 'published',
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pages_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Site settings (config-driven; admin-managed in later phases)
-- ---------------------------------------------------------------------
CREATE TABLE settings (
    setting_key   VARCHAR(100) NOT NULL,
    setting_value TEXT         NULL,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Audit log
-- ---------------------------------------------------------------------
CREATE TABLE audit_logs (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NULL,
    action      VARCHAR(100) NOT NULL,
    description TEXT         NULL,
    ip          VARCHAR(45)  NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_audit_user (user_id),
    KEY ix_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
