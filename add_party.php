<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $party_name = $_POST['party_name'] ?? '';
    $supply_date = $_POST['supply_date'] ?? '';
    $details = $_POST['details'] ?? '';
    $total_amount = $_POST['total_amount'] ?? 0;
    $advance_paid = $_POST['advance_paid'] ?? 0;

    if (empty($party_name) || empty($supply_date) || empty($total_amount)) {
        http_response_code(400);
        echo json_encode(['error' => 'Party name, supply date, and total amount are required.']);
        exit;
    }

    try {
        $pdo = getDB();

        // Insert into parties table
        $sql = "INSERT INTO parties (party_name, supply_date, details, total_amount) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$party_name, $supply_date, $details, $total_amount]);
        $party_id = $pdo->lastInsertId();

        // Insert into party_payments table if advance is paid
        if ($advance_paid > 0) {
            $sql = "INSERT INTO party_payments (party_id, payment_date, amount_paid) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$party_id, $supply_date, $advance_paid]);
        }

        http_response_code(201);
        echo json_encode(['message' => 'Party added successfully.', 'party_id' => $party_id]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>