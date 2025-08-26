<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $pdo = getDbConnection();
    
    // Get search term
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $searchTerm = "%$search%";
    
    // Get pending orders with search
    $query = "
        SELECT 
            o.order_id,
            c.name as customer_name,
            o.total_amount,
            o.status,
            (o.total_amount - COALESCE((
                SELECT SUM(amount)
                FROM payments
                WHERE order_id = o.order_id
            ), 0)) as remaining_amount
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.status != 'Fulfilled'
        AND (
            LOWER(o.order_id) LIKE LOWER(?) OR
            LOWER(c.name) LIKE LOWER(?) OR
            LOWER(c.phone) LIKE LOWER(?)
        )
        AND (o.total_amount - COALESCE((
            SELECT SUM(amount)
            FROM payments
            WHERE order_id = o.order_id
        ), 0)) > 0
        ORDER BY o.order_date DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format amounts
    foreach ($orders as &$order) {
        $order['total_amount'] = number_format($order['total_amount'], 2);
        $order['remaining_amount'] = number_format($order['remaining_amount'], 2);
    }
    
    echo json_encode([
        'success' => true,
        'orders' => $orders
    ]);

} catch (Exception $e) {
    error_log("Error getting pending orders: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load orders'
    ]);
} 