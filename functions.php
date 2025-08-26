<?php
// Format currency
function formatCurrency($amount) {
    return 'PKR ' . number_format($amount, 2);
}

// Format date
function formatDate($date) {
    return date('Y-m-d', strtotime($date));
}

// Format datetime
function formatDateTime($datetime) {
    return date('Y-m-d H:i:s', strtotime($datetime));
}

// Get order status badge
function getStatusBadge($status) {
    $badges = [
        'Pending' => 'warning',
        'Partially_Paid' => 'info',
        'Fulfilled' => 'success'
    ];
    $color = $badges[$status] ?? 'secondary';
    return "<span class='badge bg-{$color}'>{$status}</span>";
}

// Calculate remaining amount
function calculateRemainingAmount($totalAmount, $advancePayment) {
    return $totalAmount - $advancePayment;
}

// Get today's date
function getTodayDate() {
    return date('Y-m-d');
}

// Get first day of current month
function getFirstDayOfMonth() {
    return date('Y-m-01');
}

// Get last day of current month
function getLastDayOfMonth() {
    return date('Y-m-t');
}

// Validate phone number
function validatePhone($phone) {
    // Remove any non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

// Sanitize input
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate date
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// Validate amount
function validateAmount($amount) {
    return is_numeric($amount) && $amount >= 0;
}

// Get expense categories
function getExpenseCategories() {
    return [
        'Utilities',
        'Ingredients',
        'Equipment',
        'Salary',
        'Maintenance',
        'Other'
    ];
}

// Get order types
function getOrderTypes() {
    return [
        'Local',
        'Online'
    ];
}

// Format phone number
function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) === 11) {
        return substr($phone, 0, 4) . '-' . substr($phone, 4, 7);
    }
    return $phone;
}

// Generate order reference
function generateOrderReference() {
    return 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
}

// Calculate order total
function calculateOrderTotal($items) {
    $total = 0;
    foreach ($items as $item) {
        $total += $item['quantity'] * $item['unit_price'];
    }
    return $total;
}

// Validate required fields
function validateRequiredFields($data, $fields) {
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            return false;
        }
    }
    return true;
}

// Get date range for period
function getDateRange($period) {
    $end_date = date('Y-m-d');
    switch ($period) {
        case 'today':
            $start_date = $end_date;
            break;
        case 'week':
            $start_date = date('Y-m-d', strtotime('-1 week'));
            break;
        case 'month':
            $start_date = date('Y-m-01');
            break;
        case 'year':
            $start_date = date('Y-01-01');
            break;
        default:
            $start_date = $end_date;
    }
    return ['start_date' => $start_date, 'end_date' => $end_date];
}

// Log error
function logError($message, $context = []) {
    $logFile = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[$timestamp] $message $contextStr\n";
    error_log($logMessage, 3, $logFile);
}

// Send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Handle database errors
function handleDbError($e) {
    logError('Database error: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}

// Validate order data
function validateOrderData($data) {
    $errors = [];
    
    if (empty($data['customer_name'])) {
        $errors[] = 'Customer name is required';
    }
    
    if (empty($data['phone']) || !validatePhone($data['phone'])) {
        $errors[] = 'Valid phone number is required';
    }
    
    if (empty($data['order_type']) || !in_array($data['order_type'], getOrderTypes())) {
        $errors[] = 'Valid order type is required';
    }
    
    if (empty($data['delivery_date']) || !validateDate($data['delivery_date'])) {
        $errors[] = 'Valid delivery date is required';
    }
    
    if (empty($data['items']) || !is_array($data['items'])) {
        $errors[] = 'At least one item is required';
    }
    
    return $errors;
}

// Validate expense data
function validateExpenseData($data) {
    $errors = [];
    
    if (empty($data['description'])) {
        $errors[] = 'Description is required';
    }
    
    if (!validateAmount($data['amount'])) {
        $errors[] = 'Valid amount is required';
    }
    
    if (empty($data['category']) || !in_array($data['category'], getExpenseCategories())) {
        $errors[] = 'Valid category is required';
    }
    
    if (empty($data['date']) || !validateDate($data['date'])) {
        $errors[] = 'Valid date is required';
    }
    
    return $errors;
} 