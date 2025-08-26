<?php
require_once 'config.php';

// Add new customer
function addCustomer($name, $phone, $address) {
    $sql = "INSERT INTO customers (name, phone, address) VALUES ('$name', '$phone', '$address')";
    return getDB()->query($sql);
}

// Create new order
function createOrder($customerId, $orderType, $deliveryDate, $totalAmount, $advancePayment, $deliveryAddress, $notes) {
    $sql = "INSERT INTO orders (customer_id, order_type, delivery_date, total_amount, advance_payment, delivery_address, notes) 
            VALUES ($customerId, '$orderType', '$deliveryDate', $totalAmount, $advancePayment, '$deliveryAddress', '$notes')";
    return getDB()->query($sql);
}

// Add order items
function addOrderItem($orderId, $itemId, $customItemName, $quantity, $unitPrice) {
    $itemId = $itemId ? "'$itemId'" : "NULL";
    $customItemName = $customItemName ? "'$customItemName'" : "NULL";
    $sql = "INSERT INTO order_items (order_id, item_id, custom_item_name, quantity, unit_price) 
            VALUES ($orderId, $itemId, $customItemName, $quantity, $unitPrice)";
    return getDB()->query($sql);
}

// Add payment
function addPayment($orderId, $amount, $paymentType, $notes) {
    try {
        $db = getDB();
        $db->beginTransaction();
        
        // Insert payment
        $sql = "INSERT INTO payments (order_id, amount, payment_type, notes) 
                VALUES ($orderId, $amount, '$paymentType', '$notes')";
        $db->query($sql);

        // Update order status based on payment
        $sql = "UPDATE orders o 
                SET o.status = CASE 
                    WHEN o.total_amount <= (
                        SELECT COALESCE(SUM(amount), 0) 
                        FROM payments 
                        WHERE order_id = o.order_id
                    ) THEN 'Fulfilled'
                    WHEN (
                        SELECT COALESCE(SUM(amount), 0) 
                        FROM payments 
                        WHERE order_id = o.order_id
                    ) > 0 THEN 'Partially_Paid'
                    ELSE 'Pending'
                END
                WHERE o.order_id = $orderId";
        $db->query($sql);

        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

// Get customer history
function getCustomerHistory($customerId) {
    $sql = "SELECT 
                o.order_id,
                o.order_type,
                o.order_date,
                o.delivery_date,
                o.total_amount,
                o.advance_payment,
                o.remaining_amount,
                o.status,
                GROUP_CONCAT(
                    CONCAT(
                        COALESCE(oi.custom_item_name, mi.name),
                        ' (', oi.quantity, ' x ', oi.unit_price, ')'
                    ) SEPARATOR ', '
                ) as items
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN menu_items mi ON oi.item_id = mi.item_id
            WHERE o.customer_id = $customerId
            GROUP BY o.order_id
            ORDER BY o.order_date DESC";
    $result = getDB()->query($sql);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

// Get today's deliveries
function getTodaysDeliveries() {
    $sql = "SELECT * FROM todays_deliveries";
    $result = getDB()->query($sql);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

// Get orders by date range
function getOrdersByDateRange($startDate, $endDate) {
    $sql = "SELECT 
                o.*,
                c.name as customer_name,
                c.phone
            FROM orders o
            JOIN customers c ON o.customer_id = c.customer_id
            WHERE o.delivery_date BETWEEN '$startDate' AND '$endDate'
            ORDER BY o.delivery_date";
    $result = getDB()->query($sql);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

// Add expense
function addExpense($description, $amount, $category, $expenseDate) {
    $sql = "INSERT INTO expenses (description, amount, category, expense_date) 
            VALUES ('$description', $amount, '$category', '$expenseDate')";
    return getDB()->query($sql);
}

// Get expenses by date range
function getExpensesByDateRange($startDate, $endDate) {
    $sql = "SELECT * FROM expenses 
            WHERE expense_date BETWEEN '$startDate' AND '$endDate'
            ORDER BY expense_date";
    $result = getDB()->query($sql);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

// Search customer by phone or name
function searchCustomer($search) {
    $searchLike = "%$search%";
    $sql = "SELECT * FROM customers 
            WHERE phone LIKE '$search' 
            OR name LIKE '$searchLike'
            ORDER BY name";
    $result = getDB()->query($sql);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

// Get dashboard summary
function getDashboardSummary() {
    $sql = "SELECT 
                (SELECT COUNT(*) FROM orders WHERE DATE(delivery_date) = CURDATE()) as todays_orders,
                (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(order_date) = CURDATE()) as todays_sales,
                (SELECT COALESCE(SUM(remaining_amount), 0) FROM orders WHERE status != 'Fulfilled') as total_pending,
                (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE DATE(expense_date) = CURDATE()) as todays_expenses";
    $result = getDB()->query($sql);
    return $result->fetch(PDO::FETCH_ASSOC);
}
?> 