<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/functions.php';

try {
    $pdo = getDbConnection();
    
    // Calculate total pending amount from orders
    $query = "
        SELECT 
            COALESCE(SUM(
                o.total_amount - COALESCE((
                    SELECT SUM(p.amount) 
                    FROM payments p 
                    WHERE p.order_id = o.order_id
                ), 0)
            ), 0) as pending_total
        FROM orders o
        WHERE o.status != 'Fulfilled'
    ";
    
    $result = $pdo->query($query);
    $pending_total = $result->fetch(PDO::FETCH_ASSOC)['pending_total'];
    
    echo json_encode([
        'success' => true,
        'pending_total' => floatval($pending_total)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error calculating pending total: ' . $e->getMessage()
    ]);
}
?> 