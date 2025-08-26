<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = $_POST['id'];

$query = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error deleting user.']);
}
