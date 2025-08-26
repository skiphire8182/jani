<?php
require_once '../includes/db.php';

header('Content-Type: application/json');
session_start();

// Get POST data
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Basic validation
if (empty($username) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter both username and password'
    ]);
    exit;
}

try {
    $pdo = getDbConnection();
    
    // Get user from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    // Debug information
    error_log("Login attempt - Username: " . $username);
    if ($user) {
        error_log("User found - Hash in DB: " . $user['password']);
        error_log("Verifying password...");
        $verify = password_verify($password, $user['password']);
        error_log("Password verify result: " . ($verify ? 'true' : 'false'));
    } else {
        error_log("No user found with username: " . $username);
    }
    
    // Verify credentials
    if ($user && password_verify($password, $user['password'])) {
        // Update last login time
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $updateStmt->execute([$user['user_id']]);
        
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['logged_in'] = true;
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'username' => $user['username'],
                'role' => $user['role'],
                'full_name' => $user['full_name']
            ]
        ]);
    } else {
        // Log failed login attempt with more details
        error_log("Failed login attempt:");
        error_log("Username: " . $username);
        error_log("User exists: " . ($user ? 'yes' : 'no'));
        if ($user) {
            error_log("Password verification failed");
            error_log("Stored hash: " . $user['password']);
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    if ($e instanceof PDOException) {
        error_log("Database error info: " . print_r($e->errorInfo, true));
    }
    echo json_encode([
        'success' => false,
        'message' => 'Login failed. Please try again.'
    ]);
} 