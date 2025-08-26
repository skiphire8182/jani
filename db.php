<?php
$dbConfig = require_once __DIR__ . '/../config/database.php';
$dbConnection = null;

function getDbConnection() {
    global $dbConfig, $dbConnection;
    
    if ($dbConnection !== null) {
        return $dbConnection;
    }

    try {
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
        $dbConnection = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbConnection;
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        throw new PDOException("Database connection failed: " . $e->getMessage());
    }
} 