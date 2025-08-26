<?php
require_once 'includes/db.php';

try {
    $pdo = getDbConnection();
    
    // First, let's clear existing users to avoid duplicates
    $pdo->exec("TRUNCATE TABLE users");
    
    // Create admin user
    $username = 'Admin';
    $password = 'Admin@123';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, full_name, email, role, is_active) 
        VALUES (?, ?, 'System Administrator', 'admin@example.com', 'admin', 1)
    ");
    
    $stmt->execute([$username, $passwordHash]);
    
    // Create regular user
    $username = 'user';
    $password = 'user@123';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, full_name, email, role, is_active) 
        VALUES (?, ?, 'Regular User', 'user@example.com', 'user', 1)
    ");
    
    $stmt->execute([$username, $passwordHash]);
    
    echo "Users created successfully!\n";
    echo "Admin credentials: Admin / Admin@123\n";
    echo "User credentials: user / user@123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 