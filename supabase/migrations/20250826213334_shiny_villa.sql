/*
  # Jani Pakwan Center - Complete Database Schema
  
  1. Tables
    - customers: Customer information and contact details
    - menu_items: Restaurant menu items with pricing
    - orders: Order management with delivery tracking
    - order_items: Individual items within orders
    - payments: Payment tracking and history
    - expenses: Business expense management
    - users: User authentication and roles
    - parties: Supply party management
    - party_payments: Supplier payment tracking
    - khata: Account ledger system

  2. Security
    - Enable RLS on all tables
    - Add policies for role-based access control
    - Admin users can manage all data
    - Regular users can manage orders and customers

  3. Views
    - customer_history: Customer summary with order statistics
    - todays_deliveries: Orders scheduled for today
    - pending_payments: Orders with outstanding balances
*/

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS party_payments CASCADE;
DROP TABLE IF EXISTS khata CASCADE;
DROP TABLE IF EXISTS parties CASCADE;
DROP TABLE IF EXISTS payments CASCADE;
DROP TABLE IF EXISTS order_items CASCADE;
DROP TABLE IF EXISTS orders CASCADE;
DROP TABLE IF EXISTS menu_items CASCADE;
DROP TABLE IF EXISTS customers CASCADE;
DROP TABLE IF EXISTS expenses CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Drop existing views
DROP VIEW IF EXISTS customer_history CASCADE;
DROP VIEW IF EXISTS todays_deliveries CASCADE;
DROP VIEW IF EXISTS pending_payments CASCADE;

-- Create customers table
CREATE TABLE customers (
    customer_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    address TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Create menu_items table
CREATE TABLE menu_items (
    item_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    category VARCHAR(50) DEFAULT 'Main Course',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Create orders table
CREATE TABLE orders (
    order_id SERIAL PRIMARY KEY,
    customer_id INTEGER NOT NULL REFERENCES customers(customer_id) ON DELETE RESTRICT,
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
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Create order_items table
CREATE TABLE order_items (
    order_item_id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL REFERENCES orders(order_id) ON DELETE CASCADE,
    item_id INTEGER REFERENCES menu_items(item_id) ON DELETE SET NULL,
    custom_item_name VARCHAR(100),
    quantity INTEGER NOT NULL CHECK (quantity > 0),
    unit_price DECIMAL(10,2) NOT NULL CHECK (unit_price >= 0),
    total_price DECIMAL(10,2) GENERATED ALWAYS AS (quantity * unit_price) STORED
);

-- Create payments table
CREATE TABLE payments (
    payment_id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL REFERENCES orders(order_id) ON DELETE RESTRICT,
    amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
    payment_date TIMESTAMPTZ DEFAULT NOW(),
    payment_type VARCHAR(50) DEFAULT 'Regular Payment',
    notes TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Create expenses table
CREATE TABLE expenses (
    expense_id SERIAL PRIMARY KEY,
    description TEXT NOT NULL,
    amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
    expense_date DATE NOT NULL,
    category VARCHAR(50) DEFAULT 'Other',
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Create users table
CREATE TABLE users (
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

-- Create parties table
CREATE TABLE parties (
    id SERIAL PRIMARY KEY,
    party_name VARCHAR(100) NOT NULL,
    supply_date DATE NOT NULL,
    details TEXT,
    total_amount DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Create party_payments table
CREATE TABLE party_payments (
    id SERIAL PRIMARY KEY,
    party_id INTEGER NOT NULL REFERENCES parties(id) ON DELETE CASCADE,
    payment_date DATE NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL CHECK (amount_paid > 0),
    note TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Create khata table
CREATE TABLE khata (
    id SERIAL PRIMARY KEY,
    party_id INTEGER NOT NULL REFERENCES parties(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    description VARCHAR(255),
    new_amount DECIMAL(12,2) DEFAULT 0,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    remaining_amount DECIMAL(12,2) GENERATED ALWAYS AS (new_amount - paid_amount) STORED,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Create indexes for better performance
CREATE INDEX idx_customers_phone ON customers(phone);
CREATE INDEX idx_orders_customer_id ON orders(customer_id);
CREATE INDEX idx_orders_delivery_date ON orders(delivery_date);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_payments_order_id ON payments(order_id);
CREATE INDEX idx_payments_payment_date ON payments(payment_date);
CREATE INDEX idx_expenses_expense_date ON expenses(expense_date);
CREATE INDEX idx_expenses_category ON expenses(category);
CREATE INDEX idx_party_payments_party_id ON party_payments(party_id);
CREATE INDEX idx_khata_party_id ON khata(party_id);

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

-- Insert default users with hashed passwords
-- Admin password: Admin@123
-- User password: user@123
INSERT INTO users (username, password, full_name, email, role, is_active) VALUES
('Admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@example.com', 'admin', true),
('user', '$2y$10$mWK8RZTJTHewJ7HgzUUeA.p5YFTf.yW4.c8iQw3YdQHCvVJ2SFWwq', 'Regular User', 'user@example.com', 'user', true);

-- Create views
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

-- Enable Row Level Security on all tables
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

-- Create RLS policies for customers
CREATE POLICY "Users can read all customers" ON customers FOR SELECT TO authenticated USING (true);
CREATE POLICY "Users can insert customers" ON customers FOR INSERT TO authenticated WITH CHECK (true);
CREATE POLICY "Users can update customers" ON customers FOR UPDATE TO authenticated USING (true);

-- Create RLS policies for menu_items
CREATE POLICY "Users can read menu items" ON menu_items FOR SELECT TO authenticated USING (true);
CREATE POLICY "Users can manage menu items" ON menu_items FOR ALL TO authenticated USING (true);

-- Create RLS policies for orders
CREATE POLICY "Users can read all orders" ON orders FOR SELECT TO authenticated USING (true);
CREATE POLICY "Users can insert orders" ON orders FOR INSERT TO authenticated WITH CHECK (true);
CREATE POLICY "Users can update orders" ON orders FOR UPDATE TO authenticated USING (true);

-- Create RLS policies for order_items
CREATE POLICY "Users can read order items" ON order_items FOR SELECT TO authenticated USING (true);
CREATE POLICY "Users can manage order items" ON order_items FOR ALL TO authenticated USING (true);

-- Create RLS policies for payments
CREATE POLICY "Users can read payments" ON payments FOR SELECT TO authenticated USING (true);
CREATE POLICY "Users can insert payments" ON payments FOR INSERT TO authenticated WITH CHECK (true);

-- Create RLS policies for expenses (admin only)
CREATE POLICY "Admins can manage expenses" ON expenses FOR ALL TO authenticated 
USING (EXISTS (
    SELECT 1 FROM users 
    WHERE users.user_id = (auth.uid()::text)::integer 
    AND users.role = 'admin'
));

-- Create RLS policies for users
CREATE POLICY "Users can read their own data" ON users FOR SELECT TO authenticated 
USING (user_id = (auth.uid()::text)::integer);

CREATE POLICY "Admins can manage users" ON users FOR ALL TO authenticated 
USING (EXISTS (
    SELECT 1 FROM users users_1 
    WHERE users_1.user_id = (auth.uid()::text)::integer 
    AND users_1.role = 'admin'
));

-- Create RLS policies for parties
CREATE POLICY "Users can read parties" ON parties FOR SELECT TO authenticated USING (true);
CREATE POLICY "Users can manage parties" ON parties FOR ALL TO authenticated USING (true);

-- Create RLS policies for party_payments
CREATE POLICY "Users can read party payments" ON party_payments FOR SELECT TO authenticated USING (true);
CREATE POLICY "Users can manage party payments" ON party_payments FOR ALL TO authenticated USING (true);

-- Create RLS policies for khata
CREATE POLICY "Users can read khata" ON khata FOR SELECT TO authenticated USING (true);
CREATE POLICY "Users can manage khata" ON khata FOR ALL TO authenticated USING (true);

-- Create function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create trigger for orders table
CREATE TRIGGER update_orders_updated_at BEFORE UPDATE ON orders
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Create trigger for users table
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();