@@ .. @@
 <?php
 require_once '../includes/db.php';
+require_once '../includes/supabase.php';

 header('Content-Type: application/json');
 error_reporting(E_ALL);
 ini_set('display_errors', 1);

 try {
-    $pdo = getDbConnection();
+    $db = getSupabaseDB();
     
     // Get search term and sanitize it
     $search = isset($_GET['search']) ? trim($_GET['search']) : '';
     if (empty($search)) {
         throw new Exception('Search term is required');
     }
     
     // Prepare the search term for LIKE query
     $searchTerm = "%$search%";
     
     // First get customer summary with proper amount calculations
     $summaryQuery = "
         SELECT 
             c.customer_id,
             c.name as customer_name,
             c.phone,
-            c.adadress,
+            c.address,
             COUNT(DISTINCT o.order_id) as total_orders,
             SUM(o.total_amount) as total_amount,
             COALESCE(SUM(
                 (SELECT COALESCE(SUM(amount), 0)
                 FROM payments p
                 WHERE p.order_id = o.order_id)
             ), 0) as total_paid,
             SUM(
                 o.total_amount - COALESCE(
                     (SELECT SUM(amount)
                     FROM payments p
                     WHERE p.order_id = o.order_id),
                 0)
             ) as total_remaining,
             COUNT(CASE WHEN o.status = 'Fulfilled' THEN 1 END) as fulfilled_orders,
             COUNT(CASE WHEN o.status = 'Partially_Paid' THEN 1 END) as partial_orders,
             COUNT(CASE WHEN o.status = 'Pending' THEN 1 END) as pending_orders,
             MAX(o.order_date) as last_order_date
         FROM customers c
         LEFT JOIN orders o ON c.customer_id = o.customer_id
-        WHERE LOWER(c.name) LIKE LOWER(?) OR c.phone LIKE ?
+        WHERE LOWER(c.name) ILIKE LOWER(?) OR c.phone ILIKE ?
         GROUP BY c.customer_id";

-    $stmt = $pdo->prepare($summaryQuery);
-    $stmt->execute([$searchTerm, $searchTerm]);
+    $customerSummary = $db->fetchAll($summaryQuery, [$searchTerm, $searchTerm]);
     error_log("Search Term: " . $searchTerm); // Debug log
-    $customerSummary = $stmt->fetchAll(PDO::FETCH_ASSOC);
     error_log("Customer Summary: " . print_r($customerSummary, true)); // Debug log
     
     if (empty($customerSummary)) {
         echo json_encode([
             'success' => true,
             'orders' => [],
             'summary' => [],
             'message' => 'No customers found'
         ]);
         exit;
     }
     
     // Get customer orders with proper amount calculations
     $query = "
         SELECT 
             o.order_id,
             o.order_date,
             o.delivery_date,
             o.order_type,
             o.total_amount,
             (SELECT COALESCE(SUM(amount), 0)
              FROM payments p
              WHERE p.order_id = o.order_id) as paid_amount,
             (o.total_amount - COALESCE(
                 (SELECT SUM(amount)
                 FROM payments p
                 WHERE p.order_id = o.order_id),
             0)) as remaining_amount,
             o.status,
             c.name as customer_name,
             c.phone,
             c.address,
-            GROUP_CONCAT(
+            STRING_AGG(
                 CONCAT(
                     COALESCE(oi.custom_item_name, mi.name),
                     ' (', oi.quantity, ' x ', oi.unit_price, ' = ', (oi.quantity * oi.unit_price), ')'
-                ) SEPARATOR '|'
+                ), '|'
             ) as items,
             (
-                SELECT GROUP_CONCAT(
+                SELECT STRING_AGG(
                     CONCAT(
-                        amount, ' on ', DATE_FORMAT(payment_date, '%Y-%m-%d %H:%i')
-                    ) SEPARATOR '|'
+                        amount, ' on ', TO_CHAR(payment_date, 'YYYY-MM-DD HH24:MI')
+                    ), '|'
                 )
                 FROM payments p
                 WHERE p.order_id = o.order_id
             ) as payments
         FROM orders o
         JOIN customers c ON o.customer_id = c.customer_id
         LEFT JOIN order_items oi ON o.order_id = oi.order_id
         LEFT JOIN menu_items mi ON oi.item_id = mi.item_id
-        WHERE LOWER(c.name) LIKE LOWER(?) OR c.phone LIKE ?
+        WHERE LOWER(c.name) ILIKE LOWER(?) OR c.phone ILIKE ?
         GROUP BY o.order_id
         ORDER BY o.order_date DESC";

-    $stmt = $pdo->prepare($query);
-    $stmt->execute([$searchTerm, $searchTerm]);
-    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
+    $orders = $db->fetchAll($query, [$searchTerm, $searchTerm]);

     // Format the data for display
     foreach ($orders as &$order) {
         $order['items'] = $order['items'] ? array_filter(array_map('trim', explode('|', $order['items']))) : [];
         $order['payments'] = $order['payments'] ? array_filter(array_map('trim', explode('|', $order['payments']))) : [];
         
         // Ensure amounts are properly formatted as numbers first
         $order['total_amount'] = floatval($order['total_amount']);
         $order['paid_amount'] = floatval($order['paid_amount']);
         $order['remaining_amount'] = floatval($order['remaining_amount']);
         
         
     }

     // Format summary amounts consistently
     foreach ($customerSummary as &$summary) {
         
-        $summary['last_order_date'] = date('Y-m-d', strtotime($summary['last_order_date']));
+        $summary['last_order_date'] = $summary['last_order_date'] ? 
+            date('Y-m-d', strtotime($summary['last_order_date'])) : null;
     }

     echo json_encode([
         'success' => true,
         'orders' => $orders,
         'summary' => $customerSummary
     ]);

 } catch (Exception $e) {
     error_log("Error in customer_history.php: " . $e->getMessage());
     echo json_encode([
         'success' => false,
         'message' => $e->getMessage()
     ]);
 }