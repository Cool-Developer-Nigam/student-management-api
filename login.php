<?php
require_once 'config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed', null, null);
}

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Log received data for debugging
error_log("Received login request: " . print_r($data, true));

// Validate JSON
if (!$data) {
    sendResponse(false, 'Invalid JSON data', null, null);
}

// Validate required fields
$error = validateRequired($data, ['email', 'password']);
if ($error) {
    sendResponse(false, $error, null, null);
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
    
    // Log success
    error_log("Login successful for user: " . $user['id']);
    
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