<?php
// Debug output - uncomment to see if file is being accessed
// file_put_contents('debug.log', 'Simple print accessed at ' . date('Y-m-d H:i:s') . ' - Order ID: ' . $_GET['order_id'] . "\n", FILE_APPEND);

// Simple direct database connection for printing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection parameters
$db_server = 'localhost';
$db_username = 'jani_pakwan';
$db_password = 'SkipHire@8182';
$db_name = 'jani_pakwan';

// Get order ID
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

try {
    // Connect to database directly
    $pdo = new PDO("mysql:host=$db_server;dbname=$db_name;charset=utf8mb4", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get order details
    $query = "
        SELECT o.*, c.name as customer_name, c.phone, c.address 
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.order_id = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        throw new Exception("Order not found with ID: " . $order_id);
    }
    
    // Get order items
    $query = "
        SELECT oi.*, mi.name as item_name, oi.custom_item_name
        FROM order_items oi
        LEFT JOIN menu_items mi ON oi.item_id = mi.item_id
        WHERE oi.order_id = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    // Display error information
    echo "<div style='margin: 20px; padding: 20px; border: 1px solid #dc3545; border-radius: 5px; background-color: #f8d7da; color: #721c24;'>";
    echo "<h3>Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h4>Technical Details:</h4>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "<p><a href='https://jani.momz.one/index.html' class='btn btn-secondary'>Return to Dashboard</a></p>";
    echo "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> - Print</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Add Google Fonts - Noto Nastaliq Urdu -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Print-specific styles */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
            .nastaliq {
                font-family: 'Noto Nastaliq Urdu', serif;
                direction: rtl;
            }
        }
        
        /* General styles */
        body {
            background-color: #f8f9fa;
        }
        .print-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .print-section {
            padding: 15mm;
            border-bottom: 1px dashed #333;
        }
        .print-header {
            text-align: center;
            margin-bottom: 10mm;
        }
        .nastaliq {
            font-family: 'Noto Nastaliq Urdu', serif;
            direction: rtl;
        }
        .dual-lang {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .dual-lang .en {
            text-align: left;
        }
        .dual-lang .ur {
            text-align: right;
        }
        .copy-label {
            position: absolute;
            top: 5mm;
            right: 5mm;
            padding: 2mm 5mm;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3mm;
        }
        .controls {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
        }
        .order-details {
            margin-bottom: 5mm;
        }
        .customer-details {
            margin-bottom: 5mm;
        }
        .items-table {
            width: 100%;
            margin-bottom: 5mm;
            border-collapse: collapse;
        }
        .items-table th, .items-table td {
            border: 1px solid #dee2e6;
            padding: 2mm;
        }
        .items-table th {
            background-color: #f8f9fa;
        }
        .totals {
            width: 100%;
            margin-top: 5mm;
        }
        .signature {
            margin-top: 10mm;
            text-align: right;
        }
    </style>
</head>
<body>
    <!-- Print Controls -->
    <div class="controls no-print">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="https://jani.momz.one/index.html" class="btn btn-secondary ms-2">
            <i class="fas fa-home"></i> Back to Dashboard
        </a>
    </div>

    <!-- Office Copy -->
    <div class="print-container">
        <div class="copy-label">
            <span class="en">Office Copy</span>
            <span class="nastaliq"> - دفتری کاپی</span>
        </div>
        <div class="print-section">
            <div class="print-header">
                <h2>Jani Pakwan Center</h2>
                <h3 class="nastaliq">جانی پکوان سینٹر</h3>
                <p>Order #<?php echo $order_id; ?></p>
                <p><?php echo date('d-M-Y', strtotime($order['order_date'])); ?></p>
            </div>
            
            <div class="customer-details">
                <div class="row">
                    <div class="col-6">
                        <div class="dual-lang">
                            <h5 class="en">Customer Information</h5>
                            <h5 class="ur nastaliq">گاہک کی معلومات</h5>
                        </div>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                        <?php if (!empty($order['address'])): ?>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-6">
                        <div class="dual-lang">
                            <h5 class="en">Order Information</h5>
                            <h5 class="ur nastaliq">آرڈر کی معلومات</h5>
                        </div>
                        <p><strong>Order Type:</strong> <?php echo htmlspecialchars($order['order_type']); ?></p>
                        <p><strong>Order Date:</strong> <?php echo date('d-M-Y', strtotime($order['order_date'])); ?></p>
                        <p><strong>Delivery Date:</strong> <?php echo date('d-M-Y', strtotime($order['delivery_date'])); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="items-section">
                <div class="dual-lang">
                    <h5 class="en">Order Items</h5>
                    <h5 class="ur nastaliq">آرڈر کی تفصیلات</h5>
                </div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        foreach ($items as $item): 
                            $item_name = !empty($item['custom_item_name']) ? $item['custom_item_name'] : 
                                        (!empty($item['item_name']) ? $item['item_name'] : 'Custom Item');
                            $total = $item['quantity'] * $item['unit_price'];
                        ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($item_name); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>PKR <?php echo number_format($item['unit_price'], 2); ?></td>
                            <td>PKR <?php echo number_format($total, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="totals">
                <div class="row">
                    <div class="col-6 offset-6">
                        <table class="table">
                            <tr>
                                <th>Total Amount:</th>
                                <td>PKR <?php echo number_format($order['total_amount'], 2); ?> <span class="nastaliq">(کل رقم)</span></td>
                            </tr>
                            <tr>
                                <th>Advance Payment:</th>
                                <td>PKR <?php echo number_format($order['advance_payment'], 2); ?> <span class="nastaliq">(ادائیگی)</span></td>
                            </tr>
                            <tr>
                                <th>Remaining Balance:</th>
                                <td>PKR <?php echo number_format($order['remaining_amount'], 2); ?> <span class="nastaliq">(باقی رقم)</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="signature">
                <p>Authorized Signature: _______________________</p>
            </div>
        </div>
    </div>

    <!-- Page Break -->
    <div class="page-break"></div>
    
    <!-- Customer Copy -->
    <div class="print-container">
        <div class="copy-label">
            <span class="en">Customer Copy</span>
            <span class="nastaliq"> - گاہک کی کاپی</span>
        </div>
        <div class="print-section">
            <div class="print-header">
                <h2>Jani Pakwan Center</h2>
                <h3 class="nastaliq">جانی پکوان سینٹر</h3>
                <p>Order #<?php echo $order_id; ?></p>
                <p><?php echo date('d-M-Y', strtotime($order['order_date'])); ?></p>
            </div>
            
            <div class="customer-details">
                <div class="row">
                    <div class="col-6">
                        <div class="dual-lang">
                            <h5 class="en">Customer Information</h5>
                            <h5 class="ur nastaliq">گاہک کی معلومات</h5>
                        </div>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                        <?php if (!empty($order['address'])): ?>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-6">
                        <div class="dual-lang">
                            <h5 class="en">Order Information</h5>
                            <h5 class="ur nastaliq">آرڈر کی معلومات</h5>
                        </div>
                        <p><strong>Order Type:</strong> <?php echo htmlspecialchars($order['order_type']); ?></p>
                        <p><strong>Order Date:</strong> <?php echo date('d-M-Y', strtotime($order['order_date'])); ?></p>
                        <p><strong>Delivery Date:</strong> <?php echo date('d-M-Y', strtotime($order['delivery_date'])); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="items-section">
                <div class="dual-lang">
                    <h5 class="en">Order Items</h5>
                    <h5 class="ur nastaliq">آرڈر کی تفصیلات</h5>
                </div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        foreach ($items as $item): 
                            $item_name = !empty($item['custom_item_name']) ? $item['custom_item_name'] : 
                                        (!empty($item['item_name']) ? $item['item_name'] : 'Custom Item');
                            $total = $item['quantity'] * $item['unit_price'];
                        ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($item_name); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>PKR <?php echo number_format($item['unit_price'], 2); ?></td>
                            <td>PKR <?php echo number_format($total, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="totals">
                <div class="row">
                    <div class="col-6 offset-6">
                        <table class="table">
                            <tr>
                                <th>Total Amount:</th>
                                <td>PKR <?php echo number_format($order['total_amount'], 2); ?> <span class="nastaliq">(کل رقم)</span></td>
                            </tr>
                            <tr>
                                <th>Advance Payment:</th>
                                <td>PKR <?php echo number_format($order['advance_payment'], 2); ?> <span class="nastaliq">(ادائیگی)</span></td>
                            </tr>
                            <tr>
                                <th>Remaining Balance:</th>
                                <td>PKR <?php echo number_format($order['remaining_amount'], 2); ?> <span class="nastaliq">(باقی رقم)</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5>Notice:</h5>
                        <p>This is your official receipt. Please keep it for your records.</p>
                        <p>For order inquiries, please contact us with your order number.</p>
                    </div>
                </div>
            </div>
            
            <div class="nastaliq" style="text-align: center; margin-top: 20px;">
                <h3>آپ کا شکریہ</h3>
                <p>جانی پکوان سینٹر</p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Auto-print after page load (with a slight delay to ensure fonts are loaded)
            setTimeout(function() {
                window.print();
            }, 1500);
        });
    </script>
</body>
</html> 