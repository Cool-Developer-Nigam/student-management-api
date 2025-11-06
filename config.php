<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration - Railway MySQL
define('DB_HOST', 'ballast.proxy.rlwy.net');
define('DB_USER', 'root');
define('DB_PASS', 'lZmcZyERqxUqSmWVvzQrNtOhDZuKTQLe');
define('DB_NAME', 'railway');
define('DB_PORT', '16522');

// Error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

/**
 * Get database connection with enhanced error handling
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed',
            'token' => null,
            'data' => null
        ]);
        exit();
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Send JSON response with token support
 */
function sendResponse($success, $message, $data = null, $token = null) {
    http_response_code($success ? 200 : 400);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'token' => $token,
        'data' => $data
    ]);
    exit();
}

/**
 * Validate required fields with enhanced error messages
 */
function validateRequired($data, $fields) {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            return ucfirst($field) . " is required";
        }
    }
    return null;
}

/**
 * Validate authentication token
 */
function validateToken($token) {
    if (empty($token)) {
        return null;
    }
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE remember_token = ? AND token_expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return null;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    return $user;
}

/**
 * Get authorization token from headers
 */
function getAuthToken() {
    $headers = getallheaders();
    
    if (isset($headers['Authorization'])) {
        $auth = $headers['Authorization'];
        if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}
?>