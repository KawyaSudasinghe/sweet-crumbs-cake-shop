CREATE DATABASE IF NOT EXISTS cake_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cake_shop;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS review, payment, order_item, `order`, cart_item, cart, product, category, promo_code, address, `user`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE category (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    image_url VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE product (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    size VARCHAR(50) NULL,
    image_url VARCHAR(255) NULL,
    stock_qty INT NOT NULL DEFAULT 0,
    is_available BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES category(category_id) ON DELETE RESTRICT
) ENGINE=InnoDB;
