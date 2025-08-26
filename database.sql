-- Create Database
CREATE DATABASE IF NOT EXISTS jani_pakwan;
USE jani_pakwan;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS users;

-- Customers Table with consistent ID
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_phone (phone)
) ENGINE=InnoDB;

-- Menu Items Table
CREATE TABLE menu_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Orders Table with customer reference
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_type ENUM('Online', 'Local') NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivery_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    advance_payment DECIMAL(10,2) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    remaining_amount DECIMAL(10,2) GENERATED ALWAYS AS (total_amount - advance_payment) STORED,
    status ENUM('Pending', 'Partially_Paid', 'Fulfilled') DEFAULT 'Pending',
    delivery_address TEXT,
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE RESTRICT,
    INDEX idx_customer_id (customer_id),
    INDEX idx_delivery_date (delivery_date),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Order Items Table
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_id INT,
    custom_item_name VARCHAR(100),
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Payments Table
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_type VARCHAR(50) DEFAULT 'Regular Payment',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB;

-- Expenses Table
CREATE TABLE expenses (
    expense_id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_expense_date (expense_date),
    INDEX idx_category (category)
) ENGINE=InnoDB;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default admin and user accounts
INSERT INTO users (username, password, full_name, email, role, is_active) VALUES
('Admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@example.com', 'admin', 1),
('user', '$2y$10$mWK8RZTJTHewJ7HgzUUeA.p5YFTf.yW4.c8iQw3YdQHCvVJ2SFWwq', 'Regular User', NULL, 'user', 1);

-- Insert default menu items
INSERT INTO menu_items (name, price, category) VALUES
('Biryani', 0, 'Main Course'),
('Rice', 0, 'Main Course'),
('Zarda', 0, 'Dessert'),
('Kheer', 0, 'Dessert'),
('Custard', 0, 'Dessert'),
('Qorma', 0, 'Main Course'),
('Beef', 0, 'Main Course'),
('Tikka Kabab', 0, 'Main Course');

-- Create view for customer history
CREATE OR REPLACE VIEW customer_history AS
SELECT 
    c.customer_id,
    c.name,
    c.phone,
    c.address,
    COUNT(DISTINCT o.order_id) as total_orders,
    SUM(o.total_amount) as total_spent,
    SUM(o.remaining_amount) as total_pending,
    MAX(o.order_date) as last_order_date
FROM customers c
LEFT JOIN orders o ON c.customer_id = o.customer_id
GROUP BY c.customer_id;

-- Create view for today's deliveries
CREATE OR REPLACE VIEW todays_deliveries AS
SELECT 
    o.order_id,
    c.name AS customer_name,
    c.phone,
    o.order_type,
    o.total_amount,
    o.advance_payment,
    o.remaining_amount,
    o.status,
    o.delivery_address
FROM orders o
JOIN customers c ON o.customer_id = c.customer_id
WHERE DATE(o.delivery_date) = CURDATE();

-- Create view for pending payments
CREATE VIEW pending_payments AS
SELECT 
    o.order_id,
    c.name AS customer_name,
    c.phone,
    o.total_amount,
    o.advance_payment,
    o.remaining_amount,
    o.status
FROM orders o
JOIN customers c ON o.customer_id = c.customer_id
WHERE o.status IN ('Pending', 'Partially_Paid'); 