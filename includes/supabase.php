<?php
/**
 * Supabase Database Connection
 * This file handles the connection to Supabase PostgreSQL database
 */

class SupabaseDB {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            // Get Supabase connection details from environment variables
            $host = $_ENV['SUPABASE_DB_HOST'] ?? 'localhost';
            $port = $_ENV['SUPABASE_DB_PORT'] ?? '5432';
            $dbname = $_ENV['SUPABASE_DB_NAME'] ?? 'postgres';
            $username = $_ENV['SUPABASE_DB_USER'] ?? 'postgres';
            $password = $_ENV['SUPABASE_DB_PASSWORD'] ?? '';
            
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ];
            
            $this->pdo = new PDO($dsn, $username, $password, $options);
            
            // Set timezone to UTC
            $this->pdo->exec("SET timezone = 'UTC'");
            
        } catch (PDOException $e) {
            error_log("Supabase connection error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function execute($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function lastInsertId($sequence = null) {
        return $this->pdo->lastInsertId($sequence);
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollBack() {
        return $this->pdo->rollBack();
    }
    
    public function inTransaction() {
        return $this->pdo->inTransaction();
    }
}

// Global function to get database connection (backward compatibility)
function getDbConnection() {
    return SupabaseDB::getInstance()->getConnection();
}

// Helper function to get Supabase instance
function getSupabaseDB() {
    return SupabaseDB::getInstance();
}