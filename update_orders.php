<?php
require_once 'includes/db.php';

try {
    $pdo = getDbConnection();
    $sql = file_get_contents('update_orders_table.sql');
    $pdo->exec($sql);
    echo "Table 'orders' updated successfully.";
} catch (PDOException $e) {
    die("Error updating table: " . $e->getMessage());
}
?>