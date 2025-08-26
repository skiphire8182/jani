<?php
$config = require __DIR__ . '/config/database.php';

try {
    // Create DSN with explicit charset
    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=utf8mb4",
        $config['host'],
        $config['dbname']
    );
    
    // Create PDO instance
    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "Database connection successful!<br>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM customers");
    $result = $stmt->fetch();
    echo "Total customers: " . $result['total'];
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
} 