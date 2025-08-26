<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT item_id, name, price FROM menu_items GROUP BY name ORDER BY name");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'items' => $items]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching menu items: ' . $e->getMessage()]);
}
?>