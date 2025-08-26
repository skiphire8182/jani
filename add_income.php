<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $pdo = getDbConnection();
    
    // Get input data
    $orderId = intval($_POST['order_id']);
    $amount = floatval($_POST['amount']);
    $description = $pdo->quote($_POST['description'] ?? '');

    // Validate order exists and get order details
    $orderQuery = "
        SELECT 
            o.total_amount,
            COALESCE((
                SELECT SUM(amount)
                FROM payments
                WHERE order_id = o.order_id
            ), 0) as total_paid
        FROM orders o
        WHERE o.order_id = $orderId
    ";
    
    $result = $pdo->query($orderQuery);
    $orderDetails = $result->fetch(PDO::FETCH_ASSOC);

    if (!$orderDetails) {
        throw new Exception('Order not found');
    }

    $totalAmount = floatval($orderDetails['total_amount']);
    $totalPaid = floatval($orderDetails['total_paid']);
    $remainingAmount = $totalAmount - $totalPaid;

    // Validate payment amount
    if ($amount <= 0) {
        throw new Exception('Payment amount must be greater than zero');
    }

    if ($amount > $remainingAmount) {
        throw new Exception('Payment amount cannot exceed remaining amount');
    }

    if ($amount > $totalAmount) {
        throw new Exception('Payment amount cannot exceed total order amount');
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Add payment record
    $paymentQuery = "
        INSERT INTO payments (order_id, amount, payment_date, notes)
        VALUES ($orderId, $amount, NOW(), $description)
    ";
    
    $pdo->query($paymentQuery);

    // Update order status based on payment
    $newTotalPaid = $totalPaid + $amount;
    $newStatus = '';

    if ($newTotalPaid >= $totalAmount) {
        $newStatus = 'Fulfilled';
    } else if ($newTotalPaid > 0) {
        $newStatus = 'Partially_Paid';
    } else {
        $newStatus = 'Pending';
    }

    $newStatus = $pdo->quote($newStatus);
    $updateOrderQuery = "
        UPDATE orders 
        SET status = $newStatus, 
            updated_at = NOW()
        WHERE order_id = $orderId
    ";
    
    $pdo->query($updateOrderQuery);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Payment added successfully',
        'new_status' => $newStatus,
        'remaining_amount' => $totalAmount - $newTotalPaid
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 