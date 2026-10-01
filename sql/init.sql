-- ============================================================
--  TechGear – Database Init (Updated to match website)
-- ============================================================
CREATE DATABASE IF NOT EXISTS techgear_v2_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techgear_v2_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items, orders, cart_items, wishlist, products, categories, users;
SET FOREIGN_KEY_CHECKS = 1;

-- ─── CATEGORIES ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    cat_id        INT AUTO_INCREMENT PRIMARY KEY,
    cat_name      VARCHAR(100) NOT NULL,
    cat_image_url VARCHAR(255) NOT NULL,
    cat_icon      VARCHAR(50)  DEFAULT 'bx-category',
    product_count INT          NOT NULL DEFAULT 0,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO categories (cat_id, cat_name, cat_image_url, cat_icon, product_count) VALUES
(1, 'Keyboards', 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=800&q=80', 'bx-keyboard', 2),
(2, 'Mice', 'https://images.unsplash.com/photo-1527814050087-179f0011abf4?w=800&q=80', 'bx-mouse', 2),
(3, 'Headsets', 'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?w=800&q=80', 'bx-headphone', 2),
(4, 'Monitors', 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=800&q=80', 'bx-desktop', 1),
(5, 'Controllers', 'https://images.unsplash.com/photo-1600080972464-8e5f2840d437?w=800&q=80', 'bx-joystick', 1),
(6, 'Accessories', 'https://images.unsplash.com/photo-1616423640778-28d1b53229bd?w=800&q=80', 'bx-devices', 3);

-- ─── PRODUCTS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS products (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    cat_id       INT          NOT NULL,
    p_brand      VARCHAR(80)  NOT NULL,
    p_name       VARCHAR(255) NOT NULL,
    p_price      DECIMAL(10,2) NOT NULL,
    p_desc       TEXT         NULL,
    p_image_url  VARCHAR(255) NOT NULL,
    p_stock      INT          NOT NULL DEFAULT 10,
    p_tag        VARCHAR(20)  DEFAULT NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cat_id) REFERENCES categories(cat_id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO products (cat_id, p_brand, p_name, p_price, p_desc, p_image_url, p_tag) VALUES
-- Keyboards
(1, 'RAZER', 'Razer Huntsman V3 Pro', 249.99, NULL, 'https://images.unsplash.com/photo-1595225476474-87563907a212?q=80&w=500', 'NEW'),
(1, 'CORSAIR', 'Corsair K100 RGB', 199.99, NULL, 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?q=80&w=500', NULL),
-- Mice
(2, 'LOGITECH', 'Logitech G Pro X Superlight', 159.99, NULL, 'https://images.unsplash.com/photo-1615663245857-ac9310031881?q=80&w=500', 'NEW'),
(2, 'RAZER', 'Razer DeathAdder V3', 149.99, NULL, 'https://images.unsplash.com/photo-1527814050087-179f0011abf4?q=80&w=500', '-15%'),
-- Headsets
(3, 'STEELSERIES', 'Arctis Nova Pro Wireless', 349.99, NULL, 'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?q=80&w=500', 'NEW'),
(3, 'HYPERX', 'HyperX Cloud Alpha', 119.99, NULL, 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?q=80&w=500', NULL),
-- Monitors
(4, 'ASUS', 'ROG Swift PG279QM 240Hz', 699.99, NULL, 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?q=80&w=500', '-10%'),
-- Controllers
(5, 'SONY', 'DualSense Edge', 199.99, NULL, 'https://images.unsplash.com/photo-1600080972464-8e5f2840d437?q=80&w=500', 'NEW'),
-- Accessories
(6, 'ELGATO', 'Elgato Stream Deck MK.2', 149.99, NULL, 'https://images.unsplash.com/photo-1616423640778-28d1b53229bd?q=80&w=500', 'HOT'),
(6, 'RAZER', 'Razer Gigantus V2 XXL Desk Mat', 39.99, NULL, 'https://images.unsplash.com/photo-1615663245857-ac9310031881?q=80&w=500', 'NEW'),
(6, 'CORSAIR', 'Corsair ST100 RGB Headset Stand', 69.99, NULL, 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?q=80&w=500', NULL);

-- ─── USERS ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    user_id    INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(120) NOT NULL,
    email      VARCHAR(180) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    phone      VARCHAR(20)  DEFAULT NULL,
    address    TEXT         DEFAULT NULL,
    profile_img VARCHAR(255) DEFAULT NULL,
    role       ENUM('admin','customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Password for both is 'password'
INSERT INTO users (username, email, password, role, phone, address) VALUES
('Admin TechGear', 'admin@techgear.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '0999999999', 'Admin HQ'),
('Demo User',      'nattapon09072548@gmail.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '0888888888', 'Demo Address 123');

-- ─── ORDERS ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS orders (
    order_id       INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT           NOT NULL,
    total_amount   DECIMAL(10,2) NOT NULL,
    order_status   VARCHAR(50)   DEFAULT 'Pending',
    payment_method VARCHAR(50)   DEFAULT 'PromptPay',
    fullname       VARCHAR(120),
    phone          VARCHAR(20),
    email          VARCHAR(180),
    address        TEXT,
    slip_image     VARCHAR(255)  DEFAULT NULL,
    slip_status    VARCHAR(20)   DEFAULT 'pending',
    tracking_number VARCHAR(50)  DEFAULT NULL,
    created_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT           NOT NULL,
    p_id       INT           NOT NULL,
    price      DECIMAL(10,2) NOT NULL,
    quantity   INT           NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(order_id)   ON DELETE CASCADE,
    FOREIGN KEY (p_id)       REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── ORDER TRACKING (Shopee-style) ──────────────────────────
CREATE TABLE IF NOT EXISTS order_tracking (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT           NOT NULL,
    status      VARCHAR(50)   NOT NULL,
    location    VARCHAR(255)  DEFAULT NULL,
    description TEXT          DEFAULT NULL,
    lat         DECIMAL(10,7) DEFAULT NULL,
    lng         DECIMAL(10,7) DEFAULT NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── PAYMENT METHODS ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS payment_methods (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    icon_class VARCHAR(50)  NULL,
    image_url  VARCHAR(255) NULL
) ENGINE=InnoDB;

INSERT INTO payment_methods (name, icon_class, image_url) VALUES 
('PromptPay', 'bx bx-qr-scan', NULL),
('Kasikorn Bank', 'bx bxs-bank', NULL),
('Credit Card', 'bx bx-credit-card-front', NULL);

-- ─── PRODUCT REVIEWS ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS product_reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    p_id       INT           NOT NULL,
    user_id    INT           NOT NULL,
    rating     INT           NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment    TEXT          NULL,
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (p_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;
