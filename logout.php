<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
exit;
?> 