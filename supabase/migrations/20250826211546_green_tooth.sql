/*
  # Jani Pakwan Center Database Schema

  1. New Tables
    - `customers` - Customer information with contact details
    - `menu_items` - Menu items with pricing and categories
    - `orders` - Order management with delivery tracking
    - `order_items` - Individual items within orders
    - `payments` - Payment tracking for orders
    - `expenses` - Business expense management
    - `users` - User authentication and role management
    - `parties` - Supply party management
    - `party_payments` - Payment tracking for supply parties
    - `khata` - Account ledger for parties

  2. Security
    - Enable RLS on all tables
    - Add policies for authenticated users
    - Role-based access control

  3. Features
    - Comprehensive order management
    - Customer relationship tracking
    - Financial management (income/expenses)
    - Supply chain management
    - Multi-user support with roles
*/

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Customers Table
CREATE TABLE IF NOT EXISTS customers (
    customer_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    CONSTRAINT unique_phone UNIQUE (phone)
);

-- Menu Items Table
CREATE TABLE IF NOT EXISTS menu_items (
    item_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    category VARCHAR(50) DEFAULT 'Main Course',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    order_id SERIAL PRIMARY KEY,
    customer_id INTEGER NOT NULL,
    order_type VARCHAR(20) NOT NULL CHECK (order_type IN ('Online', 'Local')),
    order_date TIMESTAMPTZ DEFAULT NOW(),
    delivery_date DATE NOT NULL,
    delivery_time TIME,
    total_amount DECIMAL(10,2) NOT NULL,
    advance_payment DECIMAL(10,2) DEFAULT 0,
    remaining_amount DECIMAL(10,2) GENERATED ALWAYS AS (total_amount - advance_payment) STORED,
    status VARCHAR(20) DEFAULT 'Pending' CHECK (status IN ('Pending', 'Partially_Paid', 'Fulfilled')),
    delivery_address TEXT,
    notes TEXT,
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE RESTRICT
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL,
    item_id INTEGER,
    custom_item_name VARCHAR(100),
    quantity INTEGER NOT NULL CHECK (quantity > 0),
    unit_price DECIMAL(10,2) NOT NULL CHECK (unit_price >= 0),
    total_price DECIMAL(10,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON DELETE SET NULL
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    payment_id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL,
    amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
    payment_date TIMESTAMPTZ DEFAULT NOW(),
    payment_type VARCHAR(50) DEFAULT 'Regular Payment',
    notes TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE RESTRICT
);

-- Expenses Table
CREATE TABLE IF NOT EXISTS expenses (
    expense_id SERIAL PRIMARY KEY,
    description TEXT NOT NULL,
    amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
    expense_date DATE NOT NULL,
    category VARCHAR(50) DEFAULT 'Other',
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    role VARCHAR(20) NOT NULL DEFAULT 'user' CHECK (role IN ('admin', 'user')),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Supply Parties Table
CREATE TABLE IF NOT EXISTS parties (
    id SERIAL PRIMARY KEY,
    party_name VARCHAR(100) NOT NULL,
    supply_date DATE NOT NULL,
    details TEXT,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Party Payments Table
CREATE TABLE IF NOT EXISTS party_payments (
    id SERIAL PRIMARY KEY,
    party_id INTEGER NOT NULL,
    payment_date DATE NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL CHECK (amount_paid > 0),
    note TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE CASCADE
);

-- Khata (Ledger) Table
CREATE TABLE IF NOT EXISTS khata (
    id SERIAL PRIMARY KEY,
    party_id INTEGER NOT NULL,
    date DATE NOT NULL,
    description VARCHAR(255),
    new_amount DECIMAL(12,2) DEFAULT 0,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    remaining_amount DECIMAL(12,2) GENERATED ALWAYS AS (new_amount - paid_amount) STORED,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE CASCADE
);

-- Enable Row Level Security
ALTER TABLE customers ENABLE ROW LEVEL SECURITY;
ALTER TABLE menu_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE orders ENABLE ROW LEVEL SECURITY;
ALTER TABLE order_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE payments ENABLE ROW LEVEL SECURITY;
ALTER TABLE expenses ENABLE ROW LEVEL SECURITY;
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE parties ENABLE ROW LEVEL SECURITY;
ALTER TABLE party_payments ENABLE ROW LEVEL SECURITY;
ALTER TABLE khata ENABLE ROW LEVEL SECURITY;

-- RLS Policies for customers
CREATE POLICY "Users can read all customers"
  ON customers FOR SELECT
  TO authenticated
  USING (true);

CREATE POLICY "Users can insert customers"
  ON customers FOR INSERT
  TO authenticated
  WITH CHECK (true);

CREATE POLICY "Users can update customers"
  ON customers FOR UPDATE
  TO authenticated
  USING (true);

-- RLS Policies for menu_items
CREATE POLICY "Users can read menu items"
  ON menu_items FOR SELECT
  TO authenticated
  USING (true);

CREATE POLICY "Users can manage menu items"
  ON menu_items FOR ALL
  TO authenticated
  USING (true);

-- RLS Policies for orders
CREATE POLICY "Users can read all orders"
  ON orders FOR SELECT
  TO authenticated
  USING (true);

CREATE POLICY "Users can insert orders"
  ON orders FOR INSERT
  TO authenticated
  WITH CHECK (true);

CREATE POLICY "Users can update orders"
  ON orders FOR UPDATE
  TO authenticated
  USING (true);

-- RLS Policies for order_items
CREATE POLICY "Users can read order items"
  ON order_items FOR SELECT
  TO authenticated
  USING (true);

CREATE POLICY "Users can manage order items"
  ON order_items FOR ALL
  TO authenticated
  USING (true);

-- RLS Policies for payments
CREATE POLICY "Users can read payments"
  ON payments FOR SELECT
  TO authenticated
  USING (true);

CREATE POLICY "Users can insert payments"
  ON payments FOR INSERT
  TO authenticated
  WITH CHECK (true);

-- RLS Policies for expenses (admin only)
CREATE POLICY "Admins can manage expenses"
  ON expenses FOR ALL
  TO authenticated
  USING (
    EXISTS (
      SELECT 1 FROM users 
      WHERE users.user_id = auth.uid()::text::integer 
      AND users.role = 'admin'
    )
  );

-- RLS Policies for users (admin only)
CREATE POLICY "Admins can manage users"
  ON users FOR ALL
  TO authenticated
  USING (
    EXISTS (
      SELECT 1 FROM users 
      WHERE users.user_id = auth.uid()::text::integer 
      AND users.role = 'admin'
    )
  );

CREATE POLICY "Users can read their own data"
  ON users FOR SELECT
  TO authenticated
  USING (user_id = auth.uid()::text::integer);

-- RLS Policies for parties
CREATE POLICY "Users can read parties"
  ON parties FOR SELECT
  TO authenticated
  USING (true);

CREATE POLICY "Users can manage parties"
  ON parties FOR ALL
  TO authenticated
  USING (true);

-- RLS Policies for party_payments
CREATE POLICY "Users can read party payments"
  ON party_payments FOR SELECT
  TO authenticated
  USING (true);

CREATE POLICY "Users can manage party payments"
  ON party_payments FOR ALL
  TO authenticated
  USING (true);

-- RLS Policies for khata
CREATE POLICY "Users can read khata"
  ON khata FOR SELECT
  TO authenticated
  USING (true);

CREATE POLICY "Users can manage khata"
  ON khata FOR ALL
  TO authenticated
  USING (true);

-- Insert default menu items
INSERT INTO menu_items (name, price, category) VALUES
('Biryani', 0, 'Main Course'),
('Rice', 0, 'Main Course'),
('Zarda', 0, 'Dessert'),
('Kheer', 0, 'Dessert'),
('Custard', 0, 'Dessert'),
('Qorma', 0, 'Main Course'),
('Beef', 0, 'Main Course'),
('Tikka Kabab', 0, 'Main Course')
ON CONFLICT DO NOTHING;

-- Insert default admin user (password: Admin@123)
INSERT INTO users (username, password, full_name, email, role, is_active) VALUES
('Admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@example.com', 'admin', true),
('user', '$2y$10$mWK8RZTJTHewJ7HgzUUeA.p5YFTf.yW4.c8iQw3YdQHCvVJ2SFWwq', 'Regular User', 'user@example.com', 'user', true)
ON CONFLICT (username) DO NOTHING;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_customers_phone ON customers(phone);
CREATE INDEX IF NOT EXISTS idx_orders_customer_id ON orders(customer_id);
CREATE INDEX IF NOT EXISTS idx_orders_delivery_date ON orders(delivery_date);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_order_items_order_id ON order_items(order_id);
CREATE INDEX IF NOT EXISTS idx_payments_order_id ON payments(order_id);
CREATE INDEX IF NOT EXISTS idx_payments_payment_date ON payments(payment_date);
CREATE INDEX IF NOT EXISTS idx_expenses_expense_date ON expenses(expense_date);
CREATE INDEX IF NOT EXISTS idx_expenses_category ON expenses(category);
CREATE INDEX IF NOT EXISTS idx_party_payments_party_id ON party_payments(party_id);
CREATE INDEX IF NOT EXISTS idx_khata_party_id ON khata(party_id);

-- Create useful views
CREATE OR REPLACE VIEW customer_history AS
SELECT 
    c.customer_id,
    c.name,
    c.phone,
    c.address,
    COUNT(DISTINCT o.order_id) as total_orders,
    COALESCE(SUM(o.total_amount), 0) as total_spent,
    COALESCE(SUM(o.remaining_amount), 0) as total_pending,
    MAX(o.order_date) as last_order_date
FROM customers c
LEFT JOIN orders o ON c.customer_id = o.customer_id
GROUP BY c.customer_id, c.name, c.phone, c.address;

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
WHERE DATE(o.delivery_date) = CURRENT_DATE;

CREATE OR REPLACE VIEW pending_payments AS
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