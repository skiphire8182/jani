@@ .. @@
 <?php
 require_once '../includes/db.php';
-require_once '../includes/functions.php';
+require_once '../includes/supabase.php';

 header('Content-Type: application/json');
 error_reporting(E_ALL);
 ini_set('display_errors', 1);

 try {
-    $pdo = getDbConnection();
+    $db = getSupabaseDB();
     $method = $_SERVER['REQUEST_METHOD'];

     switch ($method) {
         case 'GET':
             // Get date range parameters
             $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
             $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

             // Basic query to get expenses
             $query = "SELECT expense_id, description, amount, expense_date, category, created_at 
                      FROM expenses 
-                     WHERE expense_date BETWEEN '$start_date' AND '$end_date' 
+                     WHERE expense_date BETWEEN ? AND ? 
                      ORDER BY expense_date DESC";
             
             error_log("Executing query: " . $query); // Debug log
-            $result = $pdo->query($query);
-            
-            if (!$result) {
-                throw new Exception("Failed to execute expenses query");
-            }
-            
-            $expenses = $result->fetchAll(PDO::FETCH_ASSOC);
+            $expenses = $db->fetchAll($query, [$start_date, $end_date]);

             // Calculate summary data directly
             $total = 0;
             $categories = [];
             foreach ($expenses as $expense) {
                 $total += floatval($expense['amount']);
                 if (!isset($categories[$expense['category']])) {
                     $categories[$expense['category']] = 0;
                 }
                 $categories[$expense['category']] += floatval($expense['amount']);
             }

             // Find top category
             $top_category = 'N/A';
             $max_amount = 0;
             foreach ($categories as $category => $amount) {
                 if ($amount > $max_amount) {
                     $max_amount = $amount;
                     $top_category = $category;
                 }
             }

             $summary = [
                 'total_entries' => count($expenses),
                 'total_expenses' => $total,
                 'average_daily' => count($expenses) > 0 ? $total / count($expenses) : 0,
                 'top_category' => $top_category
             ];

             echo json_encode([
                 'success' => true,
                 'expenses' => $expenses,
                 'summary' => $summary
             ]);
             break;

         case 'POST':
             $input = file_get_contents('php://input');
             $data = json_decode($input, true);

             if (!$data) {
                 throw new Exception("Invalid JSON data: " . json_last_error_msg());
             }

             // Validate required fields
             $required = ['description', 'amount', 'date', 'category'];
             foreach ($required as $field) {
                 if (empty($data[$field])) {
                     throw new Exception("Missing required field: {$field}");
                 }
             }

             $description = trim($data['description']);
             $amount = floatval($data['amount']);

             if ($amount <= 0) {
                 throw new Exception('Expense amount must be greater than zero');
             }
             $date = $data['date'];
             $category = trim($data['category']);

             // Simple insert query
-            $query = "INSERT INTO expenses (description, amount, expense_date, category) 
-                     VALUES ('$description', $amount, '$date', '$category')";
+            $query = "INSERT INTO expenses (description, amount, expense_date, category) 
+                     VALUES (?, ?, ?, ?)";
             
             error_log("Executing insert query: " . $query); // Debug log
-            $result = $pdo->query($query);
-            
-            if (!$result) {
-                throw new Exception("Failed to insert expense");
-            }
+            $db->execute($query, [$description, $amount, $date, $category]);

-            $id = $pdo->lastInsertId();
+            $id = $db->lastInsertId('expenses_expense_id_seq');

             echo json_encode([
                 'success' => true,
                 'message' => 'Expense added successfully',
                 'id' => $id
             ]);
             break;

         case 'DELETE':
             $input = file_get_contents('php://input');
             $data = json_decode($input, true);

             if (!$data || !isset($data['expense_id'])) {
                 throw new Exception("Missing expense_id");
             }

             $expense_id = intval($data['expense_id']);
             
             // Simple delete query
-            $query = "DELETE FROM expenses WHERE expense_id = $expense_id";
+            $query = "DELETE FROM expenses WHERE expense_id = ?";
             
             error_log("Executing delete query: " . $query); // Debug log
-            $result = $pdo->query($query);
-            
-            if (!$result) {
-                throw new Exception("Failed to delete expense");
-            }
+            $rowsAffected = $db->execute($query, [$expense_id]);

-            if ($result->rowCount() === 0) {
+            if ($rowsAffected === 0) {
                 throw new Exception("No expense found with ID: $expense_id");
             }

             echo json_encode([
                 'success' => true,
                 'message' => 'Expense deleted successfully'
             ]);
             break;

         default:
             throw new Exception("Invalid method: $method");
     }

 } catch (Exception $e) {
     error_log("Error in expenses.php: " . $e->getMessage());
     http_response_code(500);
     echo json_encode([
         'success' => false,
         'message' => 'Server error: ' . $e->getMessage()
     ]);
 }