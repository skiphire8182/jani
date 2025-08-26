<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database connection...\n";

$host = 'localhost';
$dbname = 'jani_pakwan';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

try {
    echo "Attempting to connect to MySQL server at $host...\n";
    
    // Try connecting without specifying a database first
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Successfully connected to MySQL server!\n";
    
    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $dbExists = $stmt->fetchColumn();
    
    if ($dbExists) {
        echo "Database '$dbname' exists!\n";
        
        // Connect to the specific database
        $dbPdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
        $dbPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Successfully connected to database '$dbname'!\n";
        
        // Check for tables
        $stmt = $dbPdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "Found " . count($tables) . " tables in the database:\n";
            foreach ($tables as $table) {
                echo "- $table\n";
            }
        } else {
            echo "No tables found in the database. You may need to import the database structure.\n";
            echo "Try running: mysql -u $username -p $dbname < database.sql\n";
        }
    } else {
        echo "Database '$dbname' does not exist. Creating it now...\n";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        echo "Database '$dbname' created successfully!\n";
        echo "Now you need to import the database structure. Try running:\n";
        echo "mysql -u $username -p $dbname < database.sql\n";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'refused') !== false) {
        echo "\nIt seems the MySQL server is not running. Please start your MySQL server.\n";
        echo "On Windows: Open Services and start MySQL service\n";
        echo "On Linux: sudo service mysql start\n";
        echo "On macOS: brew services start mysql\n";
    } else if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "\nAccess denied. Please check your MySQL username and password.\n";
    }
}
?> 