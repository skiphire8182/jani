<?php
require_once '../includes/db.php';

if (isset($_GET['party_id'])) {
    $party_id = (int)$_GET['party_id'];
    $db = getDbConnection();
    $stmt = $db->prepare('SELECT * FROM party_payments WHERE party_id = :party_id ORDER BY payment_date DESC');
    $stmt->execute(['party_id' => $party_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare('SELECT party_name FROM parties WHERE id = :party_id');
    $stmt->execute(['party_id' => $party_id]);
    $party_name = $stmt->fetchColumn();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'party_name' => $party_name, 'payments' => $payments]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Party ID is required.']);
}
?>