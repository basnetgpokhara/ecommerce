-- =====================================================================
--  NepMart — Multi-Vendor Marketplace  ::  ONE-FILE IMPORT (schema + data)
-- ---------------------------------------------------------------------
--  HOW TO USE IN phpMyAdmin:
--    1. Create an empty database, e.g.  nepmart   (Collation: utf8mb4_unicode_ci)
--    2. Select that database in the left pane
--    3. Import  ->  choose this file  ->  Go
--    4. In the project, copy .env.example to .env and set:
--         DB_NAME=nepmart   DB_USER=...   DB_PASS=...
--    5. Open the site, e.g.  http://localhost/nepmart
--
--  Demo logins (passwords are bcrypt-hashed below):
--    Admin     admin@nepmart.test    / admin123
--    Seller    himgadgets@nepmart.test / seller123
--    Customer  buyer@nepmart.test    / customer123
--
--  This file is idempotent: it drops & recreates every table, then
--  inserts fresh demo data. Re-import to reset.
-- =====================================================================

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

-- =====================================================================
--  DEMO DATA
-- =====================================================================
-- =====================================================================
--  DEMO DATA  (part of nepmart.sql — appended after schema)
--  Password hashes are real bcrypt ($2b$12$) generated to match:
--    admin@nepmart.test   -> admin123
--    *@nepmart.test       -> seller123   (sellers)
--    buyer@nepmart.test   -> customer123
-- =====================================================================

-- ---- Site settings ----
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name','NepMart Marketplace'),
('site_tagline','A modern multi-vendor marketplace connecting local sellers and shoppers across Nepal.'),
('currency_code','NPR'),
('currency_symbol','रू'),
('contact_email','hello@nepmart.test'),
('contact_phone','+977 1-590-0000'),
('approval_mode','auto'),
('default_commission_rate','10.00'),
('cod_enabled','1'),
('esewa_enabled','0'),
('khalti_enabled','0'),
('fonepay_enabled','0'),
('free_shipping_threshold','2000'),
('shipping_fee','150'),
('review_auto_approve','1');

-- ---- Users (passwords are bcrypt-hashed, never plain text) ----
INSERT INTO users (id,name,email,phone,password_hash,role,status) VALUES
(1,'Site Admin','admin@nepmart.test','9800000001','$2b$12$kZLEMMCzRMZ4/vZjPRF9LeuNXzhbFQDk1kskEdLU6pxoQB01Xija.','admin','active'),
(2,'Himalayan Gadgets','himgadgets@nepmart.test','9800000002','$2b$12$oRZlofFfrW.YK1kachXN4OGcln90.cq.Mpi1GU8xg1c6ts23OytSG','seller','active'),
(3,'Annapurna Apparel','apparel@nepmart.test','9800000003','$2b$12$oRZlofFfrW.YK1kachXN4OGcln90.cq.Mpi1GU8xg1c6ts23OytSG','seller','active'),
(4,'Everest Essentials','everest@nepmart.test','9800000004','$2b$12$oRZlofFfrW.YK1kachXN4OGcln90.cq.Mpi1GU8xg1c6ts23OytSG','seller','active'),
(5,'Rita Sharma','buyer@nepmart.test','9800000009','$2b$12$rOMk/nh4eGJbOub7Eyu4FunvyaqlzR0uy72MDD17OaH8fgEvup9cC','customer','active');

-- ---- Sellers ----
INSERT INTO sellers (id,user_id,shop_name,slug,shop_banner,description,contact_phone,contact_email,commission_rate,status) VALUES
(1,2,'Himalayan Gadgets','himalayan-gadgets','assets/img/shop-banner-1.svg','Electronics and gadgets from across Nepal.','9800000002','himgadgets@nepmart.test',10.00,'active'),
(2,3,'Annapurna Apparel','annapurna-apparel','assets/img/shop-banner-2.svg','Fashion and footwear for every season.','9800000003','apparel@nepmart.test',12.00,'active'),
(3,4,'Everest Essentials','everest-essentials','assets/img/shop-banner-3.svg','Home, grocery and daily essentials.','9800000004','everest@nepmart.test',8.00,'active');

-- ---- Categories (+ a few sub-categories) ----
INSERT INTO categories (id,name,slug,parent_id,image,sort_order,is_featured) VALUES
(1,'Electronics','electronics',NULL,'assets/img/cat-electronics.svg',0,1),
(2,'Fashion','fashion',NULL,'assets/img/cat-fashion.svg',1,1),
(3,'Home & Living','home-living',NULL,'assets/img/cat-home.svg',2,1),
(4,'Beauty & Health','beauty-health',NULL,'assets/img/cat-beauty.svg',3,1),
(5,'Sports','sports',NULL,'assets/img/cat-sports.svg',4,1),
(6,'Grocery','grocery',NULL,'assets/img/cat-grocery.svg',5,1),
(7,'Mobiles','mobiles',1,NULL,0,0),
(8,'Computers','computers',1,NULL,1,0),
(9,'Clothing','clothing',2,NULL,0,0),
(10,'Footwear','footwear',2,NULL,1,0);

-- ---- Products ----
INSERT INTO products (id,seller_id,category_id,name,slug,short_description,description,specifications,brand,price,discount_price,stock,sku,status,is_featured,rating_avg,rating_count) VALUES
(1,1,1,'Galaxy A55 Smartphone','galaxy-a55','Galaxy A55 Smartphone — quality product from himalayan-gadgets','High-quality Galaxy A55 Smartphone, sourced and shipped within Nepal. Backed by seller warranty and easy returns.\n\n• Genuine product\n• Fast nationwide delivery\n• 7-day return policy','Brand: Samsung\nSKU: HG-A55\nWarranty: 1 year','Samsung',28990.00,25990.00,25,'HG-A55','approved',1,4.50,18),
(2,1,1,'14-inch Ultrabook Laptop','ultrabook-14','14-inch Ultrabook Laptop — quality product from himalayan-gadgets','High-quality 14-inch Ultrabook Laptop, sourced and shipped within Nepal. Backed by seller warranty and easy returns.\n\n• Genuine product\n• Fast nationwide delivery\n• 7-day return policy','Brand: Lenovo\nSKU: HG-L14\nWarranty: 1 year','Lenovo',78990.00,72990.00,10,'HG-L14','approved',1,4.70,9),
(3,1,1,'Wireless ANC Headphones','anc-headphones','Wireless ANC Headphones — quality product from himalayan-gadgets','High-quality Wireless ANC Headphones, sourced and shipped within Nepal. Backed by seller warranty and easy returns.\n\n• Genuine product\n• Fast nationwide delivery\n• 7-day return policy','Brand: Sony\nSKU: HG-HP1\nWarranty: 1 year','Sony',7990.00,5990.00,40,'HG-HP1','approved',1,4.40,26),
(4,1,1,'Smartwatch Series 7','smartwatch-7','Smartwatch Series 7 — quality product from himalayan-gadgets','High-quality Smartwatch Series 7, sourced and shipped within Nepal. Backed by seller warranty and easy returns.\n\n• Genuine product\n• Fast nationwide delivery\n• 7-day return policy','Brand: Amazfit\nSKU: HG-SW7\nWarranty: 1 year','Amazfit',14990.00,12990.00,18,'HG-SW7','approved',0,4.20,12),
(5,2,5,'Himalayan Trail Sneakers','trail-sneakers','Himalayan Trail Sneakers — quality product from annapurna-apparel','High-quality Himalayan Trail Sneakers, sourced and shipped within Nepal. Backed by seller warranty and easy returns.\n\n• Genuine product\n• Fast nationwide delivery\n• 7-day return policy','Brand: Adidas\nSKU: AA-TS1\nWarranty: 1 year','Adidas',4990.00,3990.00,30,'AA-TS1','approved',1,4.60,21),
(6,2,2,'Down Winter Jacket','winter-jacket','Down Winter Jacket — quality product from annapurna-apparel','High-quality Down Winter Jacket, sourced and shipped within Nepal. Backed by seller warranty and easy returns.\n\n• Genuine product\n• Fast nationwide delivery\n• 7-day return policy','Brand: Northface\nSKU: AA-WJ1\nWarranty: 1 year','Northface',6990.00,4990.00,15,'AA-WJ1','approved',1,4.30,14),
(7,2,2,'Organic Cotton T-Shirt','cotton-tshirt','Organic Cotton T-Shirt — quality product from annapurna-apparel','High-quality Organic Cotton T-Shirt, sourced and shipped within Nepal. Backed by seller warranty and easy returns.\n\n• Genuine product\n• Fast nationwide delivery\n• 7-day return policy','Brand: Annapurna\nSKU: AA-TS2\nWarranty: 1 year','Annapurna',1290.00,NULL,60,'AA-TS2','approved',0,4.10,7),
(8,3,6,'Basmati Rice 5kg','basmati-rice-5kg','Basmati Rice 5kg — quality product from everest-essentials','High-quality Basmati Rice 5kg, sourced and shipped within Nepal.\n\n• Genuine product\n• Fast nationwide delivery','Brand: Everest\nSKU: EE-RICE','Everest',990.00,849.00,100,'EE-RICE','approved',0,4.00,33),
(9,3,3,'LED Desk Lamp','led-desk-lamp','LED Desk Lamp — quality product from everest-essentials','High-quality LED Desk Lamp, sourced and shipped within Nepal.\n\n• Genuine product\n• Fast nationwide delivery\n• 7-day return policy','Brand: Philips\nSKU: EE-LMP','Philips',1990.00,1490.00,22,'EE-LMP','approved',0,4.20,5),
(10,3,3,'Power Blender 1.5L','power-blender','Power Blender 1.5L — quality product from everest-essentials','High-quality Power Blender 1.5L, sourced and shipped within Nepal.\n\n• Genuine product\n• Fast nationwide delivery\n• 7-day return policy','Brand: Philips\nSKU: EE-BLD','Philips',5490.00,4790.00,14,'EE-BLD','approved',1,4.50,11),
(11,3,4,'Matte Lipstick Set','lipstick-set','Matte Lipstick Set — quality product from everest-essentials','High-quality Matte Lipstick Set, sourced and shipped within Nepal.\n\n• Genuine product\n• Fast nationwide delivery\n• 7-day return policy','Brand: Maybelline\nSKU: EE-LIP','Maybelline',1890.00,1490.00,35,'EE-LIP','approved',0,4.40,19),
(12,2,5,'Match Football Size 5','match-football','Match Football Size 5 — quality product from annapurna-apparel','High-quality Match Football Size 5, sourced and shipped within Nepal.\n\n• Genuine product\n• Fast nationwide delivery','Brand: Nike\nSKU: AA-FB5','Nike',2490.00,1990.00,20,'AA-FB5','approved',0,4.30,8);

-- ---- Product images (one primary image each) ----
INSERT INTO product_images (product_id,image_path,is_primary,sort_order) VALUES
(1,'assets/img/phone.svg',1,0),
(2,'assets/img/laptop.svg',1,0),
(3,'assets/img/headphones.svg',1,0),
(4,'assets/img/watch.svg',1,0),
(5,'assets/img/shoes.svg',1,0),
(6,'assets/img/jacket.svg',1,0),
(7,'assets/img/tshirt.svg',1,0),
(8,'assets/img/rice.svg',1,0),
(9,'assets/img/lamp.svg',1,0),
(10,'assets/img/blender.svg',1,0),
(11,'assets/img/lipstick.svg',1,0),
(12,'assets/img/football.svg',1,0);

-- ---- Banners ----
INSERT INTO banners (title,subtitle,image,link,button_text,position,sort_order,status) VALUES
('Big Sale, Local Shops','Up to 40% off electronics, fashion & more','assets/img/hero1.svg','/search?sort=newest','Shop Now','hero',0,'active'),
('Fresh from Nepal','Groceries & home essentials, delivered fast','assets/img/hero2.svg','/category/grocery','Shop Groceries','hero',1,'active'),
('Free shipping over रू 2,000','Pay with eSewa, Khalti, Fonepay or COD',NULL,'/shops','Explore Shops','promo',0,'active');

-- ---- CMS pages ----
INSERT INTO pages (title,slug,body,status) VALUES
('About Us','about','<p>NepMart is a multi-vendor marketplace built to empower Nepali sellers and make online shopping effortless for customers nationwide.</p>','published'),
('Privacy Policy','privacy','<p>We respect your privacy. Your personal data is only used to fulfil orders and improve your shopping experience.</p>','published'),
('Terms & Conditions','terms','<p>By using NepMart you agree to our terms of service. Sellers are responsible for the accuracy of their listings.</p>','published'),
('Returns & Refunds','returns','<p>Items can be returned within 7 days of delivery if unused and in original packaging. Refunds are processed to your original payment method.</p>','published'),
('Shipping','shipping','<p>We deliver across Nepal. Orders over रू 2,000 ship free. Standard delivery takes 2-5 business days.</p>','published'),
('Contact Us','contact','<p>Reach us at hello@nepmart.test or +977 1-590-0000. We are here to help 7 days a week.</p>','published');

-- ---- Address (for the demo customer) ----
INSERT INTO addresses (id,user_id,label,full_name,phone,address_line1,address_line2,city,district,postal_code,is_default) VALUES
(1,5,'Home','Rita Sharma','9800000009','Lakeside Road','Near Phewa Lake','Pokhara','Kaski','33700',1);

-- ---- A sample order (so dashboards aren't empty) ----
INSERT INTO orders (id,order_number,customer_id,shipping_name,shipping_phone,shipping_address,shipping_city,shipping_district,subtotal,shipping_fee,discount,tax,total,status,payment_method,payment_status) VALUES
(1,'NM-DEMO-000001',5,'Rita Sharma','9800000009','Lakeside Road\nNear Phewa Lake','Pokhara','Kaski',33970.00,0.00,0.00,0.00,33970.00,'confirmed','cod','pending');

INSERT INTO order_items (order_id,product_id,seller_id,product_name,product_image,quantity,unit_price,subtotal,commission_rate,commission_amount,seller_earnings,fulfillment) VALUES
(1,1,1,'Galaxy A55 Smartphone','assets/img/phone.svg',1,25990.00,25990.00,10.00,2599.00,23391.00,'processing'),
(1,5,2,'Himalayan Trail Sneakers','assets/img/shoes.svg',2,3990.00,7980.00,12.00,957.60,7022.40,'processing');

INSERT INTO payments (order_id,gateway,amount,status) VALUES
(1,'cod',33970.00,'initiated');

-- ---- A couple of approved reviews ----
INSERT INTO reviews (product_id,customer_id,rating,comment,status) VALUES
(1,5,5,'Great phone, delivered quickly to Pokhara!','approved'),
(5,5,4,'Comfortable and sturdy. Good value.','approved');
