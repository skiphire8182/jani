@@ .. @@
 <?php
 require_once '../includes/db.php';
-require_once '../includes/functions.php';
+require_once '../includes/supabase.php';

 header('Content-Type: application/json');
 error_reporting(E_ALL);
 ini_set('display_errors', 1);

 try {
     // Get JSON input and decode
     $input = json_decode(file_get_contents('php://input'), true);
     
     if (json_last_error() !== JSON_ERROR_NONE) {
         throw new Exception('Invalid JSON data: ' . json_last_error_msg());
     }

     // Validate required fields
     $required_fields = ['customer_name', 'phone', 'order_type', 'delivery_date', 'items'];
     foreach ($required_fields as $field) {
         if (empty($input[$field])) {
             throw new Exception("Missing required field: {$field}");
         }
     }

     // Validate amounts
     $totalAmount = floatval($input['total_amount']);
     $advancePayment = floatval($input['advance_payment'] ?? 0);

     if ($totalAmount <= 0) {
         throw new Exception('Total amount must be greater than zero');
     }

     if ($advancePayment < 0) {
         throw new Exception('Advance payment cannot be negative');
     }

     if ($advancePayment > $totalAmount) {
         throw new Exception('Advance payment cannot exceed total amount');
     }

     // Validate items
     if (empty($input['items']) || !is_array($input['items'])) {
         throw new Exception('Order must contain at least one item');
     }

     foreach ($input['items'] as $item) {
         if (!isset($item['quantity']) || !isset($item['unit_price'])) {
             throw new Exception('Invalid item data: missing quantity or price');
         }

         if (floatval($item['quantity']) <= 0) {
             throw new Exception('Item quantity must be greater than zero');
         }

         if (floatval($item['unit_price']) <= 0) {
             throw new Exception('Item price must be greater than zero');
         }
     }

     // Calculate remaining amount
     $remainingAmount = $totalAmount - $advancePayment;

     // Get database connection
-    $pdo = getDbConnection();
-    $pdo->beginTransaction();
+    $db = getSupabaseDB();
+    $db->beginTransaction();

     try {
         // Sanitize input values
         $customerName = htmlspecialchars($input['customer_name']);
         $phone = htmlspecialchars($input['phone']);
         $address = htmlspecialchars($input['address'] ?? '');
         $orderType = htmlspecialchars($input['order_type']);
         $deliveryDate = htmlspecialchars($input['delivery_date']);
         $deliveryTime = htmlspecialchars($input['delivery_time']);

         // Check if customer exists
-        $stmt = $pdo->prepare("SELECT customer_id FROM customers WHERE phone = :phone");
-        $stmt->execute([':phone' => $phone]);
-        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
+        $customer = $db->fetchOne("SELECT customer_id FROM customers WHERE phone = ?", [$phone]);

         if (!$customer) {
             // Create new customer
-            $stmt = $pdo->prepare("INSERT INTO customers (name, phone, address) VALUES (:name, :phone, :address)");
-            $stmt->execute([
-                ':name' => $customerName,
-                ':phone' => $phone,
-                ':address' => $address
-            ]);
-            $customerId = $pdo->lastInsertId();
+            $db->execute("INSERT INTO customers (name, phone, address) VALUES (?, ?, ?)", [
+                $customerName, $phone, $address
+            ]);
+            $customerId = $db->lastInsertId('customers_customer_id_seq');
         } else {
             $customerId = $customer['customer_id'];
         }

         // Determine order status
         $status = $advancePayment >= $totalAmount ? 'Fulfilled' : 
                  ($advancePayment > 0 ? 'Partially_Paid' : 'Pending');

         // Create order
-        $stmt = $pdo->prepare("
+        $db->execute("
             INSERT INTO orders (
                 customer_id,
                 order_type,
                 order_date,
                 delivery_date,
                 delivery_time,
                 total_amount,
                 advance_payment,
                 delivery_address,
                 status
             ) VALUES (
-                :customer_id,
-                :order_type,
+                ?,
+                ?,
                 NOW(),
-                :delivery_date,
-                :delivery_time,
-                :total_amount,
-                :advance_payment,
-                :delivery_address,
-                :status
+                ?,
+                ?,
+                ?,
+                ?,
+                ?,
+                ?
             )
-        ");
-        
-        $stmt->execute([
-            ':customer_id' => $customerId,
-            ':order_type' => $orderType,
-            ':delivery_date' => $deliveryDate,
-            ':delivery_time' => $deliveryTime,
-            ':total_amount' => $totalAmount,
-            ':advance_payment' => $advancePayment,
-            ':delivery_address' => $address,
-            ':status' => $status
-        ]);
-        $orderId = $pdo->lastInsertId();
+        ", [
+            $customerId,
+            $orderType,
+            $deliveryDate,
+            $deliveryTime,
+            $totalAmount,
+            $advancePayment,
+            $address,
+            $status
+        ]);
+        $orderId = $db->lastInsertId('orders_order_id_seq');

         // Add order items
-        $orderItemsStmt = $pdo->prepare("
-            INSERT INTO order_items (
-                order_id,
-                item_id,
-                quantity,
-                unit_price
-            ) VALUES (
-                :order_id,
-                :item_id,
-                :quantity,
-                :unit_price
-            )
-        ");

         foreach ($input['items'] as $item) {
             $menuItemId = null;
             if ($item['item_id'] === 'custom') {
                 $customItemName = htmlspecialchars($item['custom_item_name']);
                 $customItemPrice = floatval($item['unit_price']);

                 // Check if the custom item already exists
-                $checkStmt = $pdo->prepare("SELECT item_id FROM menu_items WHERE name = :name");
-                $checkStmt->execute(['name' => $customItemName]);
-                $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
+                $existingItem = $db->fetchOne("SELECT item_id FROM menu_items WHERE name = ?", [$customItemName]);

                 if ($existingItem) {
                     $menuItemId = $existingItem['item_id'];
                 } else {
                     // Insert the new custom item
-                    $insertMenuItemStmt = $pdo->prepare("
+                    $db->execute("
                         INSERT INTO menu_items (name, price, category) 
-                        VALUES (:name, :price, 'Custom')
-                    ");
-                    $insertMenuItemStmt->execute([
-                        ':name' => $customItemName,
-                        ':price' => $customItemPrice
-                    ]);
-                    $menuItemId = $pdo->lastInsertId();
+                        VALUES (?, ?, 'Custom')
+                    ", [$customItemName, $customItemPrice]);
+                    $menuItemId = $db->lastInsertId('menu_items_item_id_seq');
                 }
             } else {
                 $menuItemId = intval($item['item_id']);
             }

             $quantity = floatval($item['quantity']);
             $unitPrice = floatval($item['unit_price']);

-            $orderItemsStmt->execute([
-                ':order_id' => $orderId,
-                ':item_id' => $menuItemId,
-                ':quantity' => $quantity,
-                ':unit_price' => $unitPrice
-            ]);
+            $db->execute("
+                INSERT INTO order_items (order_id, item_id, quantity, unit_price) 
+                VALUES (?, ?, ?, ?)
+            ", [$orderId, $menuItemId, $quantity, $unitPrice]);
         }

         // If there's an advance payment, create payment record
         if ($advancePayment > 0) {
-            $stmt = $pdo->prepare("
+            $db->execute("
                 INSERT INTO payments (
                     order_id,
                     amount,
                     payment_date,
                     payment_type,
                     notes
                 ) VALUES (
-                    :order_id,
-                    :amount,
+                    ?,
+                    ?,
                     NOW(),
-                    'Advance Payment',
-                    'Initial advance payment'
+                    ?,
+                    ?
                 )
-            ");
-            
-            $stmt->execute([
-                ':order_id' => $orderId,
-                ':amount' => $advancePayment
-            ]);
+            ", [$orderId, $advancePayment, 'Advance Payment', 'Initial advance payment']);
         }

         // Commit transaction
-        $pdo->commit();
+        $db->commit();

         // Return success response
         echo json_encode([
             'success' => true,
             'message' => 'Order created successfully',
             'order_id' => $orderId,
             'status' => $status,
             'redirect_url' => '/'  // Redirect to the main application
         ]);

     } catch (Exception $e) {
-        if ($pdo->inTransaction()) {
-            $pdo->rollBack();
+        if ($db->inTransaction()) {
+            $db->rollBack();
         }
         throw new Exception('Database error: ' . $e->getMessage());
     }

 } catch (Exception $e) {
     error_log("Error creating order: " . $e->getMessage());
     http_response_code(500);
     echo json_encode([
         'success' => false,
         'message' => $e->getMessage()
     ]);
 }