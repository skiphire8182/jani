<?php
require_once '../includes/db.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if (empty($_GET['order_id'])) {
        throw new Exception('Order ID is required');
    }

    $orderId = $_GET['order_id'];
    $pdo = getDbConnection();

    // Get order details with items and payments
    $query = "
        SELECT 
            o.order_id,
            o.order_date,
            o.delivery_date,
            o.order_type,
            o.total_amount,
            o.advance_payment,
            o.remaining_amount,
            o.status,
            c.name as customer_name,
            c.phone,
            c.address,
            GROUP_CONCAT(
                CONCAT(
                    COALESCE(oi.custom_item_name, mi.name),
                    ' (', oi.quantity, ' x ', oi.unit_price, ' = ', (oi.quantity * oi.unit_price), ')'
                ) SEPARATOR '|'
            ) as items,
            (
                SELECT GROUP_CONCAT(
                    CONCAT(
                        amount, ' on ', DATE_FORMAT(payment_date, '%Y-%m-%d %H:%i')
                    ) SEPARATOR '|'
                )
                FROM payments p
                WHERE p.order_id = o.order_id
            ) as payments
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        LEFT JOIN menu_items mi ON oi.item_id = mi.item_id
        WHERE o.order_id = $orderId
        GROUP BY o.order_id";

    $result = $pdo->query($query);
    $order = $result->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Order not found');
    }

    // Format the items and payments arrays
    $order['items'] = $order['items'] ? explode('|', $order['items']) : [];
    $order['payments'] = $order['payments'] ? explode('|', $order['payments']) : [];

    // Format the dates
    $order['order_date'] = date('Y-m-d H:i', strtotime($order['order_date']));
    $order['delivery_date'] = date('Y-m-d', strtotime($order['delivery_date']));

    echo json_encode([
        'success' => true,
        'order' => $order
    ]);

} catch (Exception $e) {
    error_log("Error getting order details: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to get order details: ' . $e->getMessage()
    ]);
} 