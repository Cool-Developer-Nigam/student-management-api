<?php
require_once 'config.php';

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed', null, null);
}

// Get raw input
$rawInput = file_get_contents('php://input');

// Try to decode JSON
$data = json_decode($rawInput, true);

// If JSON decode fails, try to get from $_POST
if ($data === null) {
    $data = $_POST;
}

// If still no data, return error with details
if (empty($data) || !isset($data['email']) || !isset($data['password'])) {
    error_log("Raw input: " . $rawInput);
    error_log("Decoded data: " . print_r($data, true));
    error_log("POST data: " . print_r($_POST, true));
    sendResponse(false, 'Email and password are required', null, null);
}

$email = trim($data['email']);
$password = trim($data['password']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'Invalid email format', null, null);
}

try {
    $conn = getDBConnection();
    
    // Get user by email
    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if user exists
    if ($result->num_rows === 0) {
        sendResponse(false, 'Invalid email or password', null, null);
    }
    
    // Get user data
    $user = $result->fetch_assoc();
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        sendResponse(false, 'Invalid email or password', null, null);
    }
    
    // Generate secure token (64 characters)
    $token = bin2hex(random_bytes(32));
    
    // Set token expiration (30 days from now)
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    // Update user with token in database
    $updateStmt = $conn->prepare("UPDATE users SET remember_token = ?, token_expires_at = ? WHERE id = ?");
    $updateStmt->bind_param("ssi", $token, $expires, $user['id']);
    $updateStmt->execute();
    $updateStmt->close();
    
    // Prepare user data response
    $userData = [
        'id' => (int)$user['id'],
        'email' => $user['email']
    ];
    
    // Return success response
    sendResponse(true, 'Login successful', $userData, $token);
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    sendResponse(false, 'Server error: ' . $e->getMessage(), null, null);
}
?>
