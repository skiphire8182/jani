<?php
// Database configuration
define('DB_SERVER', 'localhost');     // Your database server
define('DB_USERNAME', 'jani_pakwan');        // Your database username
define('DB_PASSWORD', 'SkipHire@8182');            // Your database password
define('DB_NAME', 'jani_pakwan');

// Attempt to connect to MySQL database
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set character set to utf8mb4
    $pdo->exec("set names utf8mb4");
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Function to get database connection
function getDB() {
    global $pdo;
    return $pdo;
}
?> 