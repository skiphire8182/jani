@@ .. @@
 <?php
-$dbConfig = require_once __DIR__ . '/../config/database.php';
-$dbConnection = null;
+// Load environment variables if .env file exists
+if (file_exists(__DIR__ . '/../.env')) {
+    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
+    foreach ($lines as $line) {
+        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
+            list($key, $value) = explode('=', $line, 2);
+            $_ENV[trim($key)] = trim($value);
+        }
+    }
+}
+
+require_once __DIR__ . '/supabase.php';
 
 function getDbConnection() {
-    global $dbConfig, $dbConnection;
-    
-    if ($dbConnection !== null) {
-        return $dbConnection;
-    }
-
-    try {
-        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
-        $dbConnection = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
-        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
-        return $dbConnection;
-    } catch (PDOException $e) {
-        error_log("Database Connection Error: " . $e->getMessage());
-        throw new PDOException("Database connection failed: " . $e->getMessage());
-    }
+    return SupabaseDB::getInstance()->getConnection();
 }