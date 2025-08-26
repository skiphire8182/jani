<?php
// Test script for Jani Pakwan Center application
echo "Starting application tests...\n\n";

// Function to make API requests
function makeRequest($url, $method = 'GET', $data = null, $cookies = []) {
    $ch = curl_init();
    
    $options = [
        CURLOPT_URL => "http://localhost:5501/" . $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => $method
    ];
    
    if ($data && $method === 'POST') {
        $options[CURLOPT_POSTFIELDS] = is_array($data) ? http_build_query($data) : $data;
        if (is_string($data) && strpos($data, '{') === 0) {
            $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
        }
    }
    
    if (!empty($cookies)) {
        $cookieStr = '';
        foreach ($cookies as $key => $value) {
            $cookieStr .= "$key=$value; ";
        }
        $options[CURLOPT_COOKIE] = $cookieStr;
    }
    
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => $response
    ];
}

// Test 1: Login functionality
echo "Test 1: Testing login functionality...\n";
$loginData = [
    'username' => 'Admin',
    'password' => 'Admin@123'
];
$loginResponse = makeRequest('auth/login.php', 'POST', $loginData);
echo "Login response code: " . $loginResponse['code'] . "\n";
echo "Login response: " . $loginResponse['body'] . "\n\n";

$loginJson = json_decode($loginResponse['body'], true);
if (isset($loginJson['success']) && $loginJson['success'] === true) {
    echo "✅ Login test passed\n\n";
} else {
    echo "❌ Login test failed\n\n";
}

// Test 2: Dashboard data
echo "Test 2: Testing dashboard API...\n";
$dashboardResponse = makeRequest('api/dashboard.php');
echo "Dashboard response code: " . $dashboardResponse['code'] . "\n";
echo "Dashboard response: " . $dashboardResponse['body'] . "\n\n";

$dashboardJson = json_decode($dashboardResponse['body'], true);
if (isset($dashboardJson['success']) && $dashboardJson['success'] === true) {
    echo "✅ Dashboard API test passed\n\n";
} else {
    echo "❌ Dashboard API test failed\n\n";
}

// Test 3: Customer history
echo "Test 3: Testing customer history API...\n";
$historyResponse = makeRequest('api/customer_history.php');
echo "Customer history response code: " . $historyResponse['code'] . "\n";
echo "Customer history response: " . $historyResponse['body'] . "\n\n";

$historyJson = json_decode($historyResponse['body'], true);
if (isset($historyJson['success'])) {
    echo "✅ Customer history API test passed\n\n";
} else {
    echo "❌ Customer history API test failed\n\n";
}

// Test 4: Expenses API
echo "Test 4: Testing expenses API...\n";
$expensesResponse = makeRequest('api/expenses.php');
echo "Expenses response code: " . $expensesResponse['code'] . "\n";
echo "Expenses response: " . $expensesResponse['body'] . "\n\n";

$expensesJson = json_decode($expensesResponse['body'], true);
if (isset($expensesJson['success'])) {
    echo "✅ Expenses API test passed\n\n";
} else {
    echo "❌ Expenses API test failed\n\n";
}

// Test 5: Create a test order
echo "Test 5: Creating a test order...\n";
$orderData = json_encode([
    'customer_name' => 'Test Customer',
    'phone' => '1234567890',
    'order_type' => 'Local',
    'delivery_date' => date('Y-m-d'),
    'total_amount' => 1000,
    'advance_payment' => 500,
    'address' => 'Test Address',
    'items' => [
        [
            'item_id' => 'custom',
            'custom_item_name' => 'Test Item',
            'quantity' => 2,
            'unit_price' => 500
        ]
    ]
]);
$orderResponse = makeRequest('api/create_order.php', 'POST', $orderData);
echo "Order creation response code: " . $orderResponse['code'] . "\n";
echo "Order creation response: " . $orderResponse['body'] . "\n\n";

$orderJson = json_decode($orderResponse['body'], true);
if (isset($orderJson['success']) && $orderJson['success'] === true) {
    echo "✅ Order creation test passed\n\n";
    $orderId = $orderJson['order_id'] ?? null;
    
    if ($orderId) {
        // Test 6: Get the created order
        echo "Test 6: Getting the created order (ID: $orderId)...\n";
        $getOrderResponse = makeRequest('api/get_order.php?id=' . $orderId);
        echo "Get order response code: " . $getOrderResponse['code'] . "\n";
        echo "Get order response: " . $getOrderResponse['body'] . "\n\n";
        
        $getOrderJson = json_decode($getOrderResponse['body'], true);
        if (isset($getOrderJson['success']) && $getOrderJson['success'] === true) {
            echo "✅ Get order test passed\n\n";
        } else {
            echo "❌ Get order test failed\n\n";
        }
    }
} else {
    echo "❌ Order creation test failed\n\n";
}

// Summary
echo "==================================\n";
echo "Test Summary\n";
echo "==================================\n";
echo "Total tests run: 6\n";
echo "Check the results above for pass/fail status\n";
echo "==================================\n";
?> 