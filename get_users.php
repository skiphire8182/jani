<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$query = "SELECT id, username, role FROM users";
$result = $conn->query($query);

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'users' => $users]);
