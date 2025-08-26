<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $start_date = $_GET['start_date'] ?? date('Y-m-d');
    $end_date = $_GET['end_date'] ?? date('Y-m-d');

    $pdo = getDbConnection();
    
    // Get all payments with order and customer details
    $query = "
        SELECT 
            p.payment_id,
            p.order_id,
            p.amount,
            p.payment_date,
            p.payment_type,
            p.notes,
            o.order_type,
            o.total_amount,
            (
                SELECT COALESCE(SUM(amount), 0)
                FROM payments
                WHERE order_id = o.order_id
                AND payment_date <= p.payment_date
            ) as received_amount,
            o.remaining_amount,
            o.status,
            c.name as customer_name,
            c.phone
        FROM payments p
        JOIN orders o ON p.order_id = o.order_id
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE DATE(p.payment_date) BETWEEN ? AND ?
        ORDER BY p.payment_date DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$start_date, $end_date]);
    $income = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get summary statistics
    $summary_query = "
        SELECT 
            COUNT(DISTINCT p.order_id) as total_orders,
            COALESCE(SUM(p.amount), 0) as total_income,
            COALESCE(AVG(o.total_amount), 0) as average_order,
            (
                SELECT COALESCE(SUM(remaining_amount), 0)
                FROM orders 
                WHERE status != 'Fulfilled'
            ) as pending_payments
        FROM payments p
        JOIN orders o ON p.order_id = o.order_id
        WHERE DATE(p.payment_date) BETWEEN ? AND ?";

    $stmt = $pdo->prepare($summary_query);
    $stmt->execute([$start_date, $end_date]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Format numbers
    $summary['total_income'] = floatval($summary['total_income']);
    $summary['average_order'] = floatval($summary['average_order']);
    $summary['pending_payments'] = floatval($summary['pending_payments']);

    echo json_encode([
        'success' => true,
        'income' => $income,
        'summary' => $summary
    ]);

} catch (Exception $e) {
    error_log("Error in income.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load income data: ' . $e->getMessage()
    ]);
} 