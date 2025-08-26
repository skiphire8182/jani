# Jani Pakwan Center - Installation Guide

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)
- PDO PHP Extension
- JSON PHP Extension

## Installation Steps

### 1. Database Setup

1. Create a new MySQL database and user:
```sql
CREATE DATABASE jani_pakwan;
CREATE USER 'jani_pakwan'@'localhost' IDENTIFIED BY 'SkipHire@8182';
GRANT ALL PRIVILEGES ON jani_pakwan.* TO 'jani_pakwan'@'localhost';
FLUSH PRIVILEGES;
```

2. Import the database structure:
```bash
mysql -u jani_pakwan -p'SkipHire@8182' jani_pakwan < database.sql
```

3. Core Database Tables:
```sql
-- Customers Table
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu Items Table
CREATE TABLE menu_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders Table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_type ENUM('Online', 'Local') NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivery_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    advance_payment DECIMAL(10,2) DEFAULT 0,
    remaining_amount DECIMAL(10,2) GENERATED ALWAYS AS (total_amount - advance_payment) STORED,
    status ENUM('Pending', 'Partially_Paid', 'Fulfilled') DEFAULT 'Pending',
    delivery_address TEXT,
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

-- Order Items Table
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_id INT,
    custom_item_name VARCHAR(100),
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id)
);

-- Payments Table
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_type VARCHAR(50) DEFAULT 'Advance Payment',
    notes TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

-- Expenses Table
CREATE TABLE expenses (
    expense_id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    category VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_expense_date (expense_date),
    INDEX idx_category (category)
);
```

4. Initialize menu items:
```sql
INSERT INTO menu_items (name, price, category) VALUES
('Biryani', 0, 'Main Course'),
('Rice', 0, 'Main Course'),
('Zarda', 0, 'Dessert'),
('Kheer', 0, 'Dessert'),
('Custard', 0, 'Dessert'),
('Qorma', 0, 'Main Course'),
('Beef', 0, 'Main Course');
```

### 2. Application Setup

1. Copy all files to your web server directory:
```bash
/var/www/html/jani_pakwan/  # Linux
C:/xampp/htdocs/jani_pakwan/  # Windows with XAMPP
```

2. Set proper permissions (Linux/Unix):
```bash
chmod 755 -R /var/www/html/jani_pakwan
chmod 777 -R /var/www/html/jani_pakwan/uploads  # If using file uploads
```

3. Configure database connection in `config/database.php`:
```php
return [
    'host' => 'localhost',
    'dbname' => 'jani_pakwan',
    'username' => 'jani_pakwan',
    'password' => 'SkipHire@8182',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
];
```

### 3. Features

The system includes:
- Order Management
- Customer History
- Expense Tracking with categories
- Income Management with detailed reporting
- Dashboard with real-time statistics
- Date range filtering for reports
- Summary statistics for income and expenses

### 4. Testing

1. Test the following features:
- Creating new orders
- Customer history search
- Adding and managing expenses
- Viewing income reports
- Date range filtering
- Dashboard updates

2. Verify all calculations:
- Order totals
- Payment processing
- Income summaries
- Expense tracking

### 5. Troubleshooting

Common issues and solutions:
1. Database Connection:
   - Verify credentials in config/database.php
   - Check MySQL service status
   - Ensure proper permissions

2. File Permissions:
   - Set proper ownership: `chown -R www-data:www-data /var/www/html/jani_pakwan`
   - Check write permissions for uploads

3. Date Issues:
   - Verify PHP timezone in php.ini
   - Check MySQL timezone settings

For support: support@mirajsol.com

### 6. Web Server Configuration

#### Apache Configuration
Create or modify `.htaccess` file in the root directory:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]

# PHP Error Reporting (remove in production)
php_flag display_errors on
php_value error_reporting E_ALL
```

#### Nginx Configuration
Add to your server block:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 7. Directory Structure

Ensure your directory structure looks like this:
```
jani_pakwan/
├── api/
│   ├── dashboard.php
│   ├── create_order.php
│   ├── customer_history.php
│   └── expenses.php
├── css/
│   └── style.css
├── js/
│   └── main.js
├── forms/
│   └── new_order.php
├── img/
│   ├── logo.png
│   └── favicon.ico
├── config.php
├── index.html
└── database.sql
```

### 8. First-Time Setup

1. Create necessary directories if they don't exist:
```bash
mkdir css js forms img
```

2. Set proper file permissions (Linux/Unix):
```bash
chmod 644 config.php
chmod 644 css/*.css
chmod 644 js/*.js
```

### 9. Security Considerations

1. Update PHP configuration in `php.ini`:
```ini
display_errors = Off  # In production
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
max_execution_time = 30
memory_limit = 128M
post_max_size = 20M
upload_max_filesize = 10M
```

2. Database security is already configured:
```sql
# These permissions are already set during database creation
GRANT ALL PRIVILEGES ON jani_pakwan.* TO 'jani_pakwan'@'localhost';
FLUSH PRIVILEGES;
```

3. Set up SSL certificate for HTTPS (recommended for production).

### 10. Testing the Installation

1. Open your web browser and navigate to:
```
http://your-domain.com/jani_pakwan/
```

2. Test the following features:
- Dashboard loading
- Creating new orders
- Customer history search
- Expense management
- Payment processing

### 11. Troubleshooting

Common issues and solutions:

1. **Database Connection Error**
   - Verify database credentials in `config.php`
   - Check if MySQL service is running
   - Ensure database user has proper permissions

2. **API Endpoints Not Working**
   - Check PHP error logs
   - Verify .htaccess configuration
   - Ensure mod_rewrite is enabled

3. **File Permission Issues**
   - Set proper ownership: `chown -R www-data:www-data /var/www/html/jani_pakwan`
   - Check directory permissions: `ls -la`

4. **JavaScript Not Loading**
   - Clear browser cache
   - Check browser console for errors
   - Verify file paths in index.html

### 12. Support

For technical support or bug reports, please contact:
- Email: support@mirajsol.com
- Website: https://mirajsol.com

### 13. Updates and Maintenance

1. Regular maintenance tasks:
```bash
# Backup database
mysqldump -u jani_pakwan -p'SkipHire@8182' jani_pakwan > backup.sql

# Update file permissions if needed
find /var/www/html/jani_pakwan -type f -exec chmod 644 {} \;
find /var/www/html/jani_pakwan -type d -exec chmod 755 {} \;
```

2. Keep PHP and MySQL updated to latest stable versions
3. Regularly check error logs for issues
4. Perform regular database optimization

---
Copyright © 2025 Made With ♥️ Miraj Sol 