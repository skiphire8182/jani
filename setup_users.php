<?php
require_once 'includes/db.php';

header('Content-Type: text/plain');

try {
    $pdo = getDbConnection();
    
    // First, let's clear existing users to avoid duplicates
    $pdo->exec("TRUNCATE TABLE users");
    
    // Create admin user with fresh password hash
    $adminHash = password_hash('Admin@123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, full_name, email, role, is_active) 
        VALUES (?, ?, 'System Administrator', 'admin@example.com', 'admin', 1)
    ");
    $stmt->execute(['Admin', $adminHash]);
    
    // Create regular user with fresh password hash
    $userHash = password_hash('user@123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, full_name, email, role, is_active) 
        VALUES (?, ?, 'Regular User', 'user@example.com', 'user', 1)
    ");
    $stmt->execute(['user', $userHash]);
    
    // Verify the users were created
    $stmt = $pdo->query("SELECT user_id, username, role FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Users created successfully!\n\n";
    echo "Created users:\n";
    foreach ($users as $user) {
        echo "- {$user['username']} (Role: {$user['role']})\n";
    }
    echo "\nLogin credentials:\n";
    echo "Admin: Admin / Admin@123\n";
    echo "User: user / user@123\n";
    
    // For debugging, show the password hashes
    echo "\nAdmin hash: " . $adminHash . "\n";
    echo "User hash: " . $userHash . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // Additional error information
    if ($e instanceof PDOException) {
        echo "\nDatabase Error Info:\n";
        print_r($e->errorInfo);
    }
} 