<?php
// Ultra-simple print version with minimal dependencies
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db.php';

// Get order ID
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Connect to database
$conn = getDbConnection();

// Get order details
$query = "SELECT o.*, c.name as customer_name, c.phone, c.address FROM orders o
          JOIN customers c ON o.customer_id = c.customer_id
          WHERE o.order_id = " . $order_id;
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Order not found");
}

$order = mysqli_fetch_assoc($result);

// Get order items
$query = "SELECT oi.*, mi.name as item_name, oi.custom_item_name FROM order_items oi
          LEFT JOIN menu_items mi ON oi.item_id = mi.item_id
          WHERE oi.order_id = " . $order_id;
$items_result = mysqli_query($conn, $query);

$items = [];
while ($row = mysqli_fetch_assoc($items_result)) {
    $items[] = $row;
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0.3cm;
            }
            .no-print {display: none !important;}
            body {margin: 0; padding: 0; font-size: 9px;}
            h2 {font-size: 14px; margin-bottom: 2px;}
            h3 {font-size: 13px; margin-bottom: 2px;}
            h5 {font-size: 10px; margin-bottom: 2px;}
            .table {font-size: 8px; margin-bottom: 5px;}
            .table th, .table td {padding: 1px 2px;}
            .copy-divider {border-top: 1px dashed #000; margin: 5px 0; height: 1px;}
            .print-container {margin-bottom: 0.2cm; padding: 2mm;}
            .info-section {margin-bottom: 5px !important;}
            .signature-line {margin-top: 2px; padding-top: 2px;}
            .row {margin-right: -5px; margin-left: -5px;}
            .col-md-6 {padding-right: 5px; padding-left: 5px;}
        }
        
        body {font-size: 11px;}
        .nastaliq {font-family: 'Noto Nastaliq Urdu', serif; direction: rtl;}
        .print-container {width: 100%; background: white; padding: 3mm; border: 1px solid #ddd;}
        .copy-header {position: relative; border-bottom: 1px solid #ddd; margin-bottom: 5px; padding-bottom: 2px;}
        .copy-label {position: absolute; top: 0; right: 0; font-weight: bold; border: 1px solid #ddd; padding: 1px 3px; border-radius: 3px; background: #f8f9fa; font-size: 9px;}
        .info-section {font-size: 9px;}
        .info-section p {margin-bottom: 1px;}
        .table-sm th, .table-sm td {padding: 0.15rem;}
        .signature-line {margin-top: 5px; border-top: 1px solid #ddd; padding-top: 2px; text-align: right;}
    </style>
</head>
<body>
    <div class="container-fluid mt-3 mb-3 no-print">
        <button class="btn btn-primary btn-sm" onclick="window.print()">Print</button>
        <a href="index.html" class="btn btn-secondary btn-sm">Back to Dashboard</a>
    </div>

    <div class="container-fluid">
        <!-- Office Copy -->
        <div class="print-container">
            <div class="copy-header">
                <div class="copy-label">Office Copy</div>
                <div class="text-center">
                    <h2 class="nastaliq">جانی پکوان سینٹر</h2>
                    <p><strong>Order #<?php echo $order_id; ?></strong> | <?php echo date('d-M-Y', strtotime($order['order_date'])); ?></p>
                </div>
            </div>
            
            <div class="row info-section">
                <div class="col-6">
                    <h5>Customer Information</h5>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <?php if (!empty($order['address'])): ?>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-6">
                    <h5>Order Information</h5>
                    <p><strong>Type:</strong> <?php echo htmlspecialchars($order['order_type']); ?></p>
                    <p><strong>Delivery Date:</strong> <?php echo date('d-M-Y', strtotime($order['delivery_date'])); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                </div>
            </div>
            
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th style="width:5%">#</th>
                        <th style="width:45%">Item</th>
                        <th style="width:10%">Qty</th>
                        <th style="width:20%">Unit Price</th>
                        <th style="width:20%">Total</th>
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
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total Amount:</th>
                        <td>PKR <?php echo number_format($order['total_amount'], 2); ?> <span class="nastaliq">(کل رقم)</span></td>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Advance Payment:</th>
                        <td>PKR <?php echo number_format($order['advance_payment'], 2); ?> <span class="nastaliq">(ادائیگی)</span></td>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Remaining Balance:</th>
                        <td>PKR <?php echo number_format($order['remaining_amount'], 2); ?> <span class="nastaliq">(باقی رقم)</span></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="signature-line">
                <p>Authorized Signature: _______________________</p>
            </div>
        </div>
        
        <div class="copy-divider"></div>
        
        <!-- Customer Copy -->
        <div class="print-container">
            <div class="copy-header">
                <div class="copy-label">Customer Copy</div>
                <div class="text-center">
                    <h2 class="nastaliq">جانی پکوان سینٹر</h2>
                    <p><strong>Order #<?php echo $order_id; ?></strong> | <?php echo date('d-M-Y', strtotime($order['order_date'])); ?></p></div>
            </div>
            
            <div class="row info-section">
                <div class="col-6">
                    <h5>Customer Information</h5>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <?php if (!empty($order['address'])): ?>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-6">
                    <h5>Order Information</h5>
                    <p><strong>Type:</strong> <?php echo htmlspecialchars($order['order_type']); ?></p>
                    <p><strong>Delivery Date:</strong> <?php echo date('d-M-Y', strtotime($order['delivery_date'])); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                </div>
            </div>
            
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th style="width:5%">#</th>
                        <th style="width:45%">Item</th>
                        <th style="width:10%">Qty</th>
                        <th style="width:20%">Unit Price</th>
                        <th style="width:20%">Total</th>
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
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total Amount:</th>
                        <td>PKR <?php echo number_format($order['total_amount'], 2); ?> <span class="nastaliq">(کل رقم)</span></td>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Advance Payment:</th>
                        <td>PKR <?php echo number_format($order['advance_payment'], 2); ?> <span class="nastaliq">(ادائیگی)</span></td>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Remaining Balance:</th>
                        <td>PKR <?php echo number_format($order['remaining_amount'], 2); ?> <span class="nastaliq">(باقی رقم)</span></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="signature-line">
                <p>Customer Signature: _______________________</p>
                <div class="text-center mt-1 nastaliq">
                    <small>آپ کا شکریہ - جانی پکوان سینٹر</small>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() { window.print(); }, 1000);
        };
    </script>
</body>
</html> 