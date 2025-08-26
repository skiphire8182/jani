<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    $pdo = getDbConnection();
    $sql = "SELECT p.*, IFNULL(SUM(pp.amount_paid), 0) as paid_amount FROM parties p LEFT JOIN party_payments pp ON p.id = pp.party_id GROUP BY p.id ORDER BY p.supply_date DESC";
    $stmt = $pdo->query($sql);
    $parties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'parties' => $parties]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>