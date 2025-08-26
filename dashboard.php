<?php
require_once '../includes/db.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = getDbConnection();
    
    // Get today's orders count
    $result = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(order_date) = CURDATE()");
    $todays_orders = $result->fetchColumn();

    // Get today's sales
    $result = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(order_date) = CURDATE()");
    $todays_sales = $result->fetchColumn();

    // Get total pending amount
    $pending_query = "
        SELECT 
            COALESCE(SUM(
                o.total_amount - COALESCE((
                    SELECT SUM(p.amount) 
                    FROM payments p 
                    WHERE p.order_id = o.order_id
                ), 0)
            ), 0) as total_pending
        FROM orders o
        WHERE o.status != 'Fulfilled'
    ";
    $result = $pdo->query($pending_query);
    $total_pending = $result->fetchColumn();

    // Get today's date for comparison
    $today = date('Y-m-d');
    
    // Get today's expenses with debug logging
    $expenses_query = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE DATE(expense_date) = '$today'";
    error_log("Expenses Query: " . $expenses_query);
    
    $result = $pdo->query($expenses_query);
    $todays_expenses = $result->fetchColumn();
    error_log("Today's Expenses: " . $todays_expenses);

    echo json_encode([
        'success' => true,
        'todays_orders' => (int)$todays_orders,
        'todays_sales' => (float)$todays_sales,
        'total_pending' => (float)$total_pending,
        'todays_expenses' => (float)$todays_expenses,
        'debug_date' => $today // Adding this for debugging
    ]);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load dashboard data: ' . $e->getMessage()
    ]);
}
?> 