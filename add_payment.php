<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $party_id = $_POST['party_id'] ?? 0;
    $payment_date = $_POST['payment_date'] ?? '';
    $amount_paid = $_POST['amount_paid_now'] ?? 0;
    $note = $_POST['note'] ?? '';

    if (empty($party_id) || empty($payment_date) || empty($amount_paid)) {
        http_response_code(400);
        echo json_encode(['error' => 'Party, payment date, and amount are required.']);
        exit;
    }

    try {
        $pdo = getDbConnection();
        $sql = "INSERT INTO party_payments (party_id, payment_date, amount_paid, note) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$party_id, $payment_date, $amount_paid, $note]);

        http_response_code(201);
        echo json_encode(['message' => 'Payment added successfully.']);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>