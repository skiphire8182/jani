<?php
header('Content-Type: text/html');

$form = $_GET['form'] ?? '';

switch ($form) {
    case 'new_order':
        include 'new_order.php';
        break;
    
    case 'customer_history':
        include 'customer_history.php';
        break;
    
    case 'expenses':
        include 'expenses.php';
        break;
    
    case 'income':
        include 'income.php';
        break;
    
    default:
        echo '<div class="alert alert-danger">Form not found</div>';
}
?> 