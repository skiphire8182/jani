<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $party_id = $_POST['party_id'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $new_amount = $_POST['new_amount'];
    $paid_amount = $_POST['paid_amount'];

    try {
        $db = getDbConnection();
        $sql = "INSERT INTO khata (party_id, date, description, new_amount, paid_amount) 
                VALUES (:party_id, :date, :description, :new_amount, :paid_amount)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':party_id' => $party_id,
            ':date' => $date,
            ':description' => $description,
            ':new_amount' => $new_amount,
            ':paid_amount' => $paid_amount
        ]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}
